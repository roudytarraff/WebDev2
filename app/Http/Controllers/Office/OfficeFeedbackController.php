<?php

namespace App\Http\Controllers\Office;

use App\Models\Feedback;
use App\Models\Notification;
use Illuminate\Http\Request;

class OfficeFeedbackController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();

        $feedback = Feedback::with([
                'citizen',
                'request.service',
                'request.office',
            ])
            ->where('office_id', $office->id)
            ->latest()
            ->get();

        $totalFeedback = $feedback->count();
        $averageRating = $totalFeedback > 0 ? round($feedback->avg('rating'), 1) : 0;
        $fiveStarCount = $feedback->where('rating', 5)->count();
        $pendingReplies = $feedback->whereNull('office_reply')->count();

        $ratingCounts = [
            5 => $feedback->where('rating', 5)->count(),
            4 => $feedback->where('rating', 4)->count(),
            3 => $feedback->where('rating', 3)->count(),
            2 => $feedback->where('rating', 2)->count(),
            1 => $feedback->where('rating', 1)->count(),
        ];

        return view('office.feedback.index', compact(
            'office',
            'feedback',
            'totalFeedback',
            'averageRating',
            'fiveStarCount',
            'pendingReplies',
            'ratingCounts'
        ));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();

        $feedback = Feedback::with([
                'citizen',
                'request.service',
                'request.office.municipality',
                'office',
            ])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.feedback.show', compact('feedback'));
    }

    public function reply(Request $request, string $id)
    {
        $validated = $request->validate([
            'office_reply' => 'required|string|max:1000',
        ]);

        $office = $this->currentOffice();

        $feedback = Feedback::with(['request.service'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        $feedback->update([
            'office_reply' => $validated['office_reply'],
        ]);

        Notification::create([
            'user_id' => $feedback->citizen_user_id,
            'type' => 'feedback_reply',
            'title' => 'Office replied to your feedback',
            'message' => 'The office replied to your feedback for request ' . ($feedback->request->request_number ?? ''),
            'channel' => 'system',
            'is_read' => false,
        ]);

        return redirect()
            ->route('office.feedback.show', $feedback->id)
            ->with('success', 'Feedback reply saved successfully.');
    }
}