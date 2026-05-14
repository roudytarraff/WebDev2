<?php

namespace App\Http\Controllers\Office;

use App\Models\GeneratedDocument;
use App\Models\Notification;
use App\Models\RequestDocument;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OfficeRequestController extends OfficeBaseController
{
    public function index(Request $request)
    {
        $office = $this->currentOffice();
        $status = $request->query('status');

        $requests = ServiceRequest::with(['citizen', 'service', 'assignedTo', 'chat'])
            ->where('office_id', $office->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        $assignedRequests = $requests->where('assigned_to_user_id', Auth::id());
        $otherRequests = $requests->where('assigned_to_user_id', '!=', Auth::id());

        return view('office.requests.index', compact('office', 'assignedRequests', 'otherRequests', 'status'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
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

        $staffUsers = $office->staff()->with('user')->where('status', 'active')->get();
        $trackingUrl = route('tracking.show', $serviceRequest->qr_code);
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($trackingUrl);

        return view('office.requests.show', compact('office', 'serviceRequest', 'staffUsers', 'trackingUrl', 'qrImageUrl'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,in_progress,completed',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);
        $oldStatus = $serviceRequest->status;

        $serviceRequest->status = $request->status;
        $serviceRequest->assigned_to_user_id = $request->assigned_to_user_id;
        $serviceRequest->save();

        $history = new RequestStatusHistory();
        $history->request_id = $serviceRequest->id;
        $history->old_status = $oldStatus;
        $history->new_status = $request->status;
        $history->changed_by_user_id = Auth::user()->id;
        $history->note = $request->note;
        $history->changed_at = now();
        $history->save();

        Notification::create([
            'user_id' => $serviceRequest->citizen_user_id,
            'type' => 'request_status',
            'title' => 'Request status updated',
            'message' => 'Your request ' . $serviceRequest->request_number . ' is now ' . str_replace('_', ' ', $request->status) . '.',
            'channel' => 'system',
            'is_read' => false,
        ]);

        return back()->with('success', 'Request status updated successfully.');
    }

    public function uploadDocument(Request $request, string $id)
    {
        $request->validate([
            'required_document_id' => 'required|exists:service_required_documents,id',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::with('service.documents')
            ->where('office_id', $office->id)
            ->findOrFail($id);

        $allowedDocument = $serviceRequest->service->documents
            ->where('id', (int) $request->required_document_id)
            ->first();

        if (! $allowedDocument) {
            return back()->withErrors(['required_document_id' => 'This document does not belong to the selected service.'])->withInput();
        }

        $path = $request->file('document')->store('request-documents', 'public');

        $document = new RequestDocument();
        $document->request_id = $serviceRequest->id;
        $document->required_document_id = $request->required_document_id;
        $document->uploaded_by_user_id = Auth::user()->id;
        $document->file_name = $request->file('document')->getClientOriginalName();
        $document->file_path = $path;
        $document->file_type = $request->file('document')->getClientOriginalExtension();
        $document->document_role = 'office_upload';
        $document->uploaded_at = now();
        $document->save();

        Notification::create([
            'user_id' => $serviceRequest->citizen_user_id,
            'type' => 'document_upload',
            'title' => 'New document uploaded',
            'message' => 'The office uploaded a document for request ' . $serviceRequest->request_number . '.',
            'channel' => 'system',
            'is_read' => false,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function downloadDocument(string $id, string $documentId)
    {
        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);
        $document = RequestDocument::where('request_id', $serviceRequest->id)->findOrFail($documentId);

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

        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::with(['citizen', 'office.municipality', 'service', 'payments'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        if (! $serviceRequest->qr_code) {
            $serviceRequest->qr_code = 'QR-' . $serviceRequest->request_number;
            $serviceRequest->save();
        }

        $documentType = $request->document_type;
        $fileName = $documentType . '-' . $serviceRequest->request_number . '.pdf';
        $filePath = 'generated-documents/' . $fileName;
        $trackingUrl = route('tracking.show', $serviceRequest->qr_code);

        $pdf = Pdf::loadView('pdfs.generated_document', compact('serviceRequest', 'documentType', 'trackingUrl'));
        Storage::disk('public')->put($filePath, $pdf->output());

        GeneratedDocument::updateOrCreate([
            'request_id' => $serviceRequest->id,
            'document_type' => $documentType,
        ], [
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);

        return back()->with('success', ucfirst($documentType) . ' PDF generated successfully.');
    }

    public function downloadGeneratedDocument(string $id, string $documentId)
    {
        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);
        $document = GeneratedDocument::where('request_id', $serviceRequest->id)->findOrFail($documentId);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'The generated PDF was not found on disk.']);
        }

        return response()->download(storage_path('app/public/' . $document->file_path));
    }
}
