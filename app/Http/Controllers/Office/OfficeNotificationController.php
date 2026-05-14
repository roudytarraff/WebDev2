<?php

namespace App\Http\Controllers\Office;

use App\Events\NotificationSent;
use App\Mail\StaffNoticeMail;
use App\Models\Notification;
use App\Models\User;
use App\Services\FcmService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OfficeNotificationController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();

        // Current user's own notifications, unread first
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->orderByRaw('is_read ASC')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        // Staff list for the create-notification form
        $staffUserIds = $office->staff()->pluck('user_id')->toArray();
        $users        = User::whereIn('id', $staffUserIds)->orderBy('first_name')->get();

        return view('office.notifications.index', compact('office', 'notifications', 'unreadCount', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'channel' => 'required|in:email,sms,system',
        ]);

        $office = $this->currentOffice();

        if (! $office->staff()->where('user_id', $request->user_id)->exists()) {
            return back()->withErrors(['user_id' => 'This user does not belong to your office.'])->withInput();
        }

        $notification = Notification::create([
            'user_id'  => $request->user_id,
            'type'     => 'office_notice',
            'title'    => $request->title,
            'message'  => $request->message,
            'channel'  => $request->channel,
            'is_read'  => false,
        ]);

        $recipient = $notification->user;

        // Always broadcast in-app (real-time badge + feed)
        broadcast(new NotificationSent($notification));

        // Deliver via the chosen channel
        if ($request->channel === 'email' && $recipient?->email) {
            Mail::to($recipient->email)->queue(new StaffNoticeMail($notification));
        }

        if ($request->channel === 'sms' && $recipient?->phone) {
            app(SmsService::class)->send(
                $recipient->phone,
                "{$request->title}: {$request->message}",
                'office_notice',
                $recipient->id
            );
        }

        // FCM push regardless of channel (best-effort)
        app(FcmService::class)->notifyUser($recipient, $request->title, $request->message);

        return back()->with('success', 'Notification sent successfully.');
    }

    public function markRead(string $id)
    {
        Notification::where('user_id', Auth::id())->findOrFail($id)
            ->update(['is_read' => true]);

        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
