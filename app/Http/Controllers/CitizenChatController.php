<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['request.service', 'office', 'messages'])
            ->where('citizen_user_id', Auth::id())
            ->latest()
            ->get();

        return view('citizen.chats.index', compact('chats'));
    }

    public function show(string $id)
    {
        $chat = Chat::with(['request.service', 'office', 'messages.sender'])
            ->where('citizen_user_id', Auth::id())
            ->findOrFail($id);

        return view('citizen.chats.show', compact('chat'));
    }

    public function messages(Request $request, string $id)
    {
        $chat    = Chat::where('citizen_user_id', Auth::id())->findOrFail($id);
        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::with('sender')
            ->where('chat_id', $chat->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => $this->payload($m));

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

        $chat = Chat::where('citizen_user_id', Auth::id())
            ->where('status', 'open')
            ->findOrFail($id);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $message = ChatMessage::create([
            'chat_id'        => $chat->id,
            'sender_user_id' => Auth::id(),
            'message_text'   => $request->message_text,
            'attachment_path'=> $path,
            'sent_at'        => now(),
        ]);

        $message->load('sender');
        try {
            broadcast(new ChatMessageSent($message))->toOthers();
        } catch (\Throwable) {}

        // Notify office staff
        $officeStaffIds = $chat->office->staff()->pluck('user_id');
        foreach ($officeStaffIds as $staffUserId) {
            Notification::create([
                'user_id' => $staffUserId,
                'type'    => 'chat_message',
                'title'   => 'New message from citizen',
                'message' => Auth::user()->full_name . ' sent a message on request ' . $chat->request->request_number,
                'channel' => 'system',
                'is_read' => false,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->payload($message)]);
        }

        return back()->with('success', 'Message sent.');
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
