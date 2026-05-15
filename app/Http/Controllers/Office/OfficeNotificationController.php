<?php

namespace App\Http\Controllers\Office;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class OfficeNotificationController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $staffUserIds = $office->staff()->pluck('user_id')->toArray();

        $notifications = Notification::with('user')
            ->whereIn('user_id', $staffUserIds)
            ->latest()
            ->get();

        $users = User::whereIn('id', $staffUserIds)->orderBy('first_name')->get();

        return view('office.notifications.index', compact('office', 'notifications', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'channel' => 'required|in:email,sms,system',
        ]);

        $office = $this->currentOffice();
        $belongsToOffice = $office->staff()->where('user_id', $request->user_id)->exists();

        if (! $belongsToOffice) {
            return back()->withErrors(['user_id' => 'This user does not belong to your office.'])->withInput();
        }

        $notification = new Notification();
        $notification->user_id = $request->user_id;
        $notification->type = 'office_notice';
        $notification->title = $request->title;
        $notification->message = $request->message;
        $notification->channel = $request->channel;
        $notification->is_read = false;
        $notification->save();

        return back()->with('success', 'Notification created successfully.');
    }

    public function markRead(string $id)
    {
        $office = $this->currentOffice();
        $staffUserIds = $office->staff()->pluck('user_id')->toArray();

        $notification = Notification::whereIn('user_id', $staffUserIds)->findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return back()->with('success', 'Notification marked as read.');
    }
}
