<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\OfficeStaff;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenFeedbackController extends Controller
{
    public function index()
    {
        $feedback = Feedback::with([
                'request.service',
                'request.office.municipality',
                'office',
            ])
            ->where('citizen_user_id', Auth::id())
            ->latest()
            ->get();

        return view('citizen.feedback.index', compact('feedback'));
    }

    public function create(ServiceRequest $serviceRequest)
    {
        $serviceRequest = ServiceRequest::with([
                'service',
                'office.municipality',
                'feedback',
                'appointments',
            ])
            ->where('citizen_user_id', Auth::id())
            ->findOrFail($serviceRequest->id);

        $hasCompletedAppointment = $serviceRequest->appointments()
            ->where('status', 'completed')
            ->exists();

        if ($serviceRequest->status !== 'completed' && ! $hasCompletedAppointment) {
            return redirect()
                ->route('citizen.requests.show', $serviceRequest->id)
                ->withErrors([
                    'feedback' => 'You can only submit feedback after the request or appointment is completed.',
                ]);
        }

        if ($serviceRequest->feedback) {
            return redirect()
                ->route('citizen.requests.show', $serviceRequest->id)
                ->withErrors([
                    'feedback' => 'You already submitted feedback for this request.',
                ]);
        }

        return view('citizen.feedback.create', compact('serviceRequest'));
    }

    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        $serviceRequest = ServiceRequest::with([
                'service',
                'office.staff',
                'feedback',
                'appointments',
            ])
            ->where('citizen_user_id', Auth::id())
            ->findOrFail($serviceRequest->id);

        $hasCompletedAppointment = $serviceRequest->appointments()
            ->where('status', 'completed')
            ->exists();

        if ($serviceRequest->status !== 'completed' && ! $hasCompletedAppointment) {
            return redirect()
                ->route('citizen.requests.show', $serviceRequest->id)
                ->withErrors([
                    'feedback' => 'You can only submit feedback after the request or appointment is completed.',
                ]);
        }

        if ($serviceRequest->feedback) {
            return redirect()
                ->route('citizen.requests.show', $serviceRequest->id)
                ->withErrors([
                    'feedback' => 'You already submitted feedback for this request.',
                ]);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = Feedback::create([
            'request_id' => $serviceRequest->id,
            'citizen_user_id' => Auth::id(),
            'office_id' => $serviceRequest->office_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'office_reply' => null,
        ]);

        $staffUsers = OfficeStaff::where('office_id', $serviceRequest->office_id)
            ->where('status', 'active')
            ->get();

        foreach ($staffUsers as $staff) {
            Notification::create([
                'user_id' => $staff->user_id,
                'type' => 'feedback_received',
                'title' => 'New feedback received',
                'message' => 'A citizen rated request ' . $serviceRequest->request_number . ' with ' . $feedback->rating . ' stars.',
                'channel' => 'system',
                'is_read' => false,
            ]);
        }

        return redirect()
            ->route('citizen.requests.show', $serviceRequest->id)
            ->with('success', 'Thank you. Your feedback was submitted successfully.');
    }
}