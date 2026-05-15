<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\NotificationSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isOfficeStaff()) {
            $office = $this->resolveOffice();
            $chats = Chat::with(['citizen', 'request.service', 'messages'])
                ->where('office_id', $office->id)
                ->latest()
                ->get();
            return view('chat.index', compact('chats', 'office'));
        }

        $office = null;
        $chats = Chat::with(['request.service', 'office', 'messages'])
            ->where('citizen_user_id', $user->id)
            ->latest()
            ->get();

        return view('chat.index', compact('chats', 'office'));
    }

    public function create()
    {
        if (Auth::user()->isOfficeStaff()) {
            return redirect()->route('office.chats.index');
        }

        $requests = ServiceRequest::with(['office', 'service'])
            ->where('citizen_user_id', Auth::id())
            ->get();

        return view('chat.create', compact('requests'));
    }

    public function store(Request $request)
    {
        $request->validate(['request_id' => 'required|exists:service_requests,id']);

        $serviceRequest = ServiceRequest::where('citizen_user_id', Auth::id())
            ->findOrFail($request->request_id);

        $chat = Chat::firstOrCreate(
            ['request_id' => $serviceRequest->id],
            [
                'citizen_user_id' => Auth::id(),
                'office_id'       => $serviceRequest->office_id,
                'status'          => 'open',
            ]
        );

        return redirect()->route('chat.show', $chat->id);
    }

    public function show(string $id)
    {
        $chat = $this->findAuthorizedChat($id);
        $chat->load(['request.service', 'office', 'messages.sender', 'citizen']);

        return view('chat.show', compact('chat'));
    }

    public function messages(Request $request, string $id)
    {
        $chat    = $this->findAuthorizedChat($id);
        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::with('sender')
            ->where('chat_id', $chat->id)
            ->when($afterId > 0, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn($m) => $this->payload($m));

        return response()->json([
            'messages' => $messages,
            'last_id'  => $messages->last()['id'] ?? $afterId,
        ]);
    }

    public function storeMessage(Request $request, string $id)
    {
        $request->validate([
            'message_text' => 'required_without:attachment|string|nullable',
            'attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $chat = $this->findAuthorizedChat($id);
        abort_if($chat->status !== 'open', 403, 'This chat is closed.');

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $message = ChatMessage::create([
            'chat_id'         => $chat->id,
            'sender_user_id'  => Auth::id(),
            'message_text'    => $request->message_text,
            'attachment_path' => $path,
            'sent_at'         => now(),
        ]);

        $message->load('sender');
        try {
            broadcast(new ChatMessageSent($message))->toOthers();
        } catch (\Throwable) {}

        $user = Auth::user();

        if ($user->isOfficeStaff()) {
            $notification = Notification::create([
                'user_id' => $chat->citizen_user_id,
                'type'    => 'chat_message',
                'title'   => 'New message from office',
                'message' => $user->full_name . ' replied on request ' . ($chat->request->request_number ?? ''),
                'channel' => 'system',
                'is_read' => false,
            ]);
            try {
                broadcast(new NotificationSent($notification));
                app(FcmService::class)->notifyUser($chat->citizen, 'New Message', $notification->message);
            } catch (\Throwable) {}
        } else {
            foreach ($chat->office->staff()->pluck('user_id') as $staffUserId) {
                Notification::create([
                    'user_id' => $staffUserId,
                    'type'    => 'chat_message',
                    'title'   => 'New message from citizen',
                    'message' => $user->full_name . ' sent a message on request ' . ($chat->request->request_number ?? ''),
                    'channel' => 'system',
                    'is_read' => false,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->payload($message)]);
        }

        return back()->with('success', 'Message sent.');
    }

    private function findAuthorizedChat(string $id): Chat
    {
        $user = Auth::user();

        if ($user->isOfficeStaff()) {
            return Chat::where('office_id', $this->resolveOffice()->id)->findOrFail($id);
        }

        return Chat::where('citizen_user_id', $user->id)->findOrFail($id);
    }

    private function resolveOffice()
    {
        $staff = Auth::user()->officeStaff()
            ->where('status', 'active')
            ->with('office')
            ->first();

        abort_unless($staff, 403, 'No active office assignment found.');

        return $staff->office;
    }

    private function payload(ChatMessage $m): array
    {
        return [
            'id'             => $m->id,
            'sender_name'    => $m->sender->full_name ?? 'User',
            'is_mine'        => $m->sender_user_id === Auth::id(),
            'message_text'   => $m->message_text,
            'attachment_url' => $m->attachment_path ? asset('storage/' . $m->attachment_path) : null,
            'sent_at'        => optional($m->sent_at)->format('Y-m-d H:i'),
        ];
    }
}
