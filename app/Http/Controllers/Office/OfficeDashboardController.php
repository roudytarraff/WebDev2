<?php

namespace App\Http\Controllers\Office;

use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\ServiceRequest;

class OfficeDashboardController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();

        $requestsCount = ServiceRequest::where('office_id', $office->id)->count();

        $pendingRequests = ServiceRequest::where('office_id', $office->id)
            ->where('status', 'pending')
            ->count();

        $completedRequests = ServiceRequest::where('office_id', $office->id)
            ->where('status', 'completed')
            ->count();

        $appointmentsToday = Appointment::where('office_id', $office->id)
            ->whereHas('slot', fn ($query) => $query->whereDate('slot_date', today()))
            ->count();

        $revenue = Payment::whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->where('status', 'success')
            ->sum('amount');

        $averageRating = Feedback::where('office_id', $office->id)->avg('rating');

        $recentRequests = ServiceRequest::with(['citizen', 'service', 'assignedTo'])
            ->where('office_id', $office->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('office.dashboard', compact(
            'office',
            'requestsCount',
            'pendingRequests',
            'completedRequests',
            'appointmentsToday',
            'revenue',
            'averageRating',
            'recentRequests'
        ));
    }
}