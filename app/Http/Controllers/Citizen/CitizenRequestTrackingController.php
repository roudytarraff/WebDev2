<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\GeneratedDocument;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CitizenRequestTrackingController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['pending', 'approved', 'rejected', 'in_progress', 'completed'];

        $statusFilter = $request->query('status');
        $search = trim($request->query('search', ''));

        $totalRequests = ServiceRequest::where('citizen_user_id', Auth::id())->count();

        $pendingRequests = ServiceRequest::where('citizen_user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        $inProgressRequests = ServiceRequest::where('citizen_user_id', Auth::id())
            ->where('status', 'in_progress')
            ->count();

        $completedRequests = ServiceRequest::where('citizen_user_id', Auth::id())
            ->where('status', 'completed')
            ->count();

      

        $query = ServiceRequest::with([
                'office.municipality',
                'service',
                'assignedTo',
                'feedback',
                'appointments',
            ])
            ->withCount([
                'documents',
                'payments',
                'appointments',
                'statusHistory as status_updates_count',
            ])
            ->where('citizen_user_id', Auth::id());

        

        if ($statusFilter && in_array($statusFilter, $statuses)) {
            $query->where('status', $statusFilter);
        }

      
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('request_number', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('office', function ($officeQuery) use ($search) {
                        $officeQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $requests = $query
            ->latest('submitted_at')
            ->latest()
            ->get();

        return view('citizen.requests.index', [
            'requests' => $requests,
            'statuses' => $statuses,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'inProgressRequests' => $inProgressRequests,
            'completedRequests' => $completedRequests,
        ]);
    }

    public function show(string $id)
    {
     

        $serviceRequest = ServiceRequest::with([
                'citizen',
                'office.municipality',
                'office.address',
                'service.category',
                'service.documents',
                'assignedTo',
                'statusHistory.changedBy',
                'documents.requiredDocument',
                'documents.uploadedBy',
                'payments.transactions',
                'appointments.slot.service',
                'generatedDocuments',
                'feedback.office',
            ])
            ->where('citizen_user_id', Auth::id())
            ->findOrFail($id);

        return view('citizen.requests.show', [
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function downloadDocument(string $id, string $documentId)
    {
        

        $serviceRequest = ServiceRequest::where('citizen_user_id', Auth::id())
            ->findOrFail($id);

        $document = RequestDocument::where('request_id', $serviceRequest->id)
            ->findOrFail($documentId);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors([
                'document' => 'The uploaded file was not found on disk.',
            ]);
        }

        $fullPath = storage_path('app/public/' . $document->file_path);

        return response()->download($fullPath, $document->file_name);
    }

    public function downloadGeneratedDocument(string $id, string $documentId)
    {
        

        $serviceRequest = ServiceRequest::where('citizen_user_id', Auth::id())
            ->findOrFail($id);

        $document = GeneratedDocument::where('request_id', $serviceRequest->id)
            ->findOrFail($documentId);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors([
                'document' => 'The generated PDF was not found on disk.',
            ]);
        }

        $fullPath = storage_path('app/public/' . $document->file_path);

        return response()->download($fullPath);
    }
}