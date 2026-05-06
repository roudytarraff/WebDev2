<?php

namespace App\Http\Controllers\Office;

use App\Models\Feedback;
use Illuminate\Http\Request;

class OfficeFeedbackController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $feedback = Feedback::with(['citizen', 'request.service'])
            ->where('office_id', $office->id)
            ->latest()
            ->get();

        return view('office.feedback.index', compact('office', 'feedback'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
        $feedback = Feedback::with(['citizen', 'request.service'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.feedback.show', compact('feedback'));
    }

    public function reply(Request $request, string $id)
    {
        $request->validate([
            'office_reply' => 'required|string',
        ]);

        $office = $this->currentOffice();
        $feedback = Feedback::where('office_id', $office->id)->findOrFail($id);
        $feedback->office_reply = $request->office_reply;
        $feedback->save();

        return back()->with('success', 'Feedback reply saved successfully.');
    }
}
