<?php

namespace App\Http\Controllers\Office;

use App\Events\DocumentUploaded;
use App\Events\NotificationSent;
use App\Mail\StatusChangedMail;
use App\Models\GeneratedDocument;
use App\Models\Notification;
use App\Models\RequestDocument;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use App\Services\FcmService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OfficeRequestController extends OfficeBaseController
{
    public function index(Request $request)
    {
        $office  = $this->currentOffice();
        $status  = $request->query('status');

        $requests = ServiceRequest::with(['citizen', 'service', 'assignedTo', 'chat'])
            ->where('office_id', $office->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $assignedRequests = $requests->where('assigned_to_user_id', Auth::id());
        $otherRequests    = $requests->where('assigned_to_user_id', '!=', Auth::id());

        return view('office.requests.index', compact('office', 'assignedRequests', 'otherRequests', 'status'));
    }

    public function show(string $id)
    {
        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::with([
            'citizen',
            'service.documents',
            'assignedTo',
            'documents.requiredDocument',
            'documents.uploadedBy',
            'statusHistory.changedBy',
            'payments',
            'appointments.slot',
            'feedback',
            'chat.messages.sender',
            'generatedDocuments',
        ])->where('office_id', $office->id)->findOrFail($id);

        if (! $serviceRequest->qr_code) {
            $serviceRequest->qr_code = 'QR-' . $serviceRequest->request_number;
            $serviceRequest->save();
        }

        $staffUsers  = $office->staff()->with('user')->where('status', 'active')->get();
        $trackingUrl = route('tracking.show', $serviceRequest->qr_code);
        $qrImageUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($trackingUrl);

        return view('office.requests.show', compact('office', 'serviceRequest', 'staffUsers', 'trackingUrl', 'qrImageUrl'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status'               => 'required|in:pending,approved,rejected,in_progress,completed',
            'assigned_to_user_id'  => 'nullable|exists:users,id',
            'note'                 => 'nullable|string',
        ]);

        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::with(['citizen', 'service', 'statusHistory'])
            ->where('office_id', $office->id)
            ->findOrFail($id);
        $oldStatus = $serviceRequest->status;

        $serviceRequest->status               = $request->status;
        $serviceRequest->assigned_to_user_id  = $request->assigned_to_user_id;
        $serviceRequest->save();

        RequestStatusHistory::create([
            'request_id'          => $serviceRequest->id,
            'old_status'          => $oldStatus,
            'new_status'          => $request->status,
            'changed_by_user_id'  => Auth::id(),
            'note'                => $request->note,
            'changed_at'          => now(),
        ]);

        $citizen      = $serviceRequest->citizen;
        $statusLabel  = str_replace('_', ' ', $request->status);

        $notification = Notification::create([
            'user_id'  => $citizen->id,
            'type'     => 'request_status',
            'title'    => 'Request status updated',
            'message'  => "Your request {$serviceRequest->request_number} is now {$statusLabel}.",
            'channel'  => 'system',
            'is_read'  => false,
        ]);

        // Broadcast in-app notification
        broadcast(new NotificationSent($notification));

        // Queued email (honor user preferences — skip if citizen has no email)
        if ($citizen->email) {
            Mail::to($citizen->email)->queue(new StatusChangedMail($serviceRequest));
        }

        // SMS for critical statuses
        if (in_array($request->status, ['approved', 'rejected', 'completed']) && $citizen->phone) {
            app(SmsService::class)->send(
                $citizen->phone,
                "Your request {$serviceRequest->request_number} is now {$statusLabel}.",
                'status_update',
                $citizen->id
            );
        }

        // FCM push notification
        app(FcmService::class)->notifyUser(
            $citizen,
            'Request Updated',
            "Request {$serviceRequest->request_number} is now {$statusLabel}."
        );

        return back()->with('success', 'Request status updated successfully.');
    }

    public function uploadDocument(Request $request, string $id)
    {
        $request->validate([
            'required_document_id' => 'required|exists:service_required_documents,id',
            'document'             => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::with(['service.documents', 'citizen'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        $allowedDocument = $serviceRequest->service->documents
            ->where('id', (int) $request->required_document_id)
            ->first();

        if (! $allowedDocument) {
            return back()->withErrors(['required_document_id' => 'This document does not belong to the selected service.'])->withInput();
        }

        $path = $request->file('document')->store('request-documents', 'public');

        $document = RequestDocument::create([
            'request_id'          => $serviceRequest->id,
            'required_document_id'=> $request->required_document_id,
            'uploaded_by_user_id' => Auth::id(),
            'file_name'           => $request->file('document')->getClientOriginalName(),
            'file_path'           => $path,
            'file_type'           => $request->file('document')->getClientOriginalExtension(),
            'document_role'       => 'office_upload',
            'uploaded_at'         => now(),
        ]);

        $citizen      = $serviceRequest->citizen;
        $notification = Notification::create([
            'user_id'  => $citizen->id,
            'type'     => 'document_upload',
            'title'    => 'New document uploaded',
            'message'  => "The office uploaded a document for request {$serviceRequest->request_number}.",
            'channel'  => 'system',
            'is_read'  => false,
        ]);

        broadcast(new NotificationSent($notification));
        broadcast(new DocumentUploaded($serviceRequest, $document));

        app(FcmService::class)->notifyUser(
            $citizen,
            'Document Uploaded',
            "A new document was uploaded for request {$serviceRequest->request_number}."
        );

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function downloadDocument(string $id, string $documentId)
    {
        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);
        $document       = RequestDocument::where('request_id', $serviceRequest->id)->findOrFail($documentId);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'The uploaded file was not found on disk.']);
        }

        return response()->download(storage_path('app/public/' . $document->file_path), $document->file_name);
    }

    public function generateDocument(Request $request, string $id)
    {
        $request->validate([
            'document_type' => 'required|in:certificate,receipt,approval',
        ]);

        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::with(['citizen', 'office.municipality', 'service', 'payments'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        if (! $serviceRequest->qr_code) {
            $serviceRequest->qr_code = 'QR-' . $serviceRequest->request_number;
            $serviceRequest->save();
        }

        $documentType = $request->document_type;
        $fileName     = $documentType . '-' . $serviceRequest->request_number . '.pdf';
        $filePath     = 'generated-documents/' . $fileName;
        $trackingUrl  = route('tracking.show', $serviceRequest->qr_code);

        $pdf = Pdf::loadView('pdfs.generated_document', compact('serviceRequest', 'documentType', 'trackingUrl'));
        Storage::disk('public')->put($filePath, $pdf->output());

        GeneratedDocument::updateOrCreate(
            ['request_id' => $serviceRequest->id, 'document_type' => $documentType],
            ['file_path' => $filePath, 'generated_at' => now()]
        );

        return back()->with('success', ucfirst($documentType) . ' PDF generated successfully.');
    }

    public function downloadGeneratedDocument(string $id, string $documentId)
    {
        $office         = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);
        $document       = GeneratedDocument::where('request_id', $serviceRequest->id)->findOrFail($documentId);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'The generated PDF was not found on disk.']);
        }

        return response()->download(storage_path('app/public/' . $document->file_path));
    }
}
