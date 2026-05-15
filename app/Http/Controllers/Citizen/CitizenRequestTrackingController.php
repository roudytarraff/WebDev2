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
        $search = trim((string) $request->query('search'));

        $baseQuery = ServiceRequest::where('citizen_user_id', Auth::id());

        $totalRequests = (clone $baseQuery)->count();
        $pendingRequests = (clone $baseQuery)->where('status', 'pending')->count();
        $inProgressRequests = (clone $baseQuery)->where('status', 'in_progress')->count();
        $completedRequests = (clone $baseQuery)->where('status', 'completed')->count();

        $requests = ServiceRequest::with([
                'office.municipality',
                'service',
                'assignedTo',
            ])
            ->withCount([
                'documents',
                'payments',
                'appointments',
                'statusHistory as status_updates_count',
            ])
            ->where('citizen_user_id', Auth::id())
            ->when($statusFilter, function ($query) use ($statusFilter, $statuses) {
                if (in_array($statusFilter, $statuses, true)) {
                    $query->where('status', $statusFilter);
                }
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('request_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('service', function ($serviceQuery) use ($search) {
                            $serviceQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('office', function ($officeQuery) use ($search) {
                            $officeQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('submitted_at')
            ->latest()
            ->get();

        return view('citizen.requests.index', compact(
            'requests',
            'statuses',
            'statusFilter',
            'search',
            'totalRequests',
            'pendingRequests',
            'inProgressRequests',
            'completedRequests'
        ));
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
            ])
            ->where('citizen_user_id', Auth::id())
            ->findOrFail($id);

        return view('citizen.requests.show', compact('serviceRequest'));
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

        return response()->download(
            storage_path('app/public/' . $document->file_path),
            $document->file_name
        );
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

        return response()->download(storage_path('app/public/' . $document->file_path));
    }
}