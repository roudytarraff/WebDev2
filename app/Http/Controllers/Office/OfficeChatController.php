<?php

namespace App\Http\Controllers\Office;

use App\Events\ChatMessageSent;
use App\Events\NotificationSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeChatController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $chats = Chat::with(['citizen', 'request.service', 'messages'])
            ->where('office_id', $office->id)
            ->latest()
            ->get();

        return view('office.chats.index', compact('office', 'chats'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
        $chat = Chat::with(['citizen', 'request.service', 'messages.sender'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.chats.show', compact('chat'));
    }

    public function openForRequest(string $id)
    {
        $office = $this->currentOffice();
        $serviceRequest = ServiceRequest::where('office_id', $office->id)->findOrFail($id);

        $chat = Chat::firstOrCreate([
            'request_id' => $serviceRequest->id,
        ], [
            'citizen_user_id' => $serviceRequest->citizen_user_id,
            'office_id' => $office->id,
            'status' => 'open',
        ]);

        return redirect()->route('office.chats.show', $chat->id);
    }

    public function messages(Request $request, string $id)
    {
        $office = $this->currentOffice();
        $chat = Chat::where('office_id', $office->id)->findOrFail($id);
        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::with('sender')
            ->where('chat_id', $chat->id)
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn ($message) => $this->messagePayload($message));

        return response()->json([
            'messages' => $messages,
            'last_id' => $messages->last()['id'] ?? $afterId,
        ]);
    }

    public function storeMessage(Request $request, string $id)
    {
        $request->validate([
            'message_text' => 'required_without:attachment|string|nullable',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $office = $this->currentOffice();
        $chat = Chat::where('office_id', $office->id)->findOrFail($id);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $message = new ChatMessage();
        $message->chat_id = $chat->id;
        $message->sender_user_id = Auth::user()->id;
        $message->message_text = $request->message_text;
        $message->attachment_path = $path;
        $message->sent_at = now();
        $message->save();

        $message->load('sender');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messagePayload($message),
            ]);
        }

        return back()->with('success', 'Message sent successfully.');
    }

    private function messagePayload(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_name' => $message->sender->full_name ?? 'User',
            'is_mine' => $message->sender_user_id === Auth::id(),
            'message_text' => $message->message_text,
            'attachment_url' => $message->attachment_path ? asset('storage/' . $message->attachment_path) : null,
            'sent_at' => optional($message->sent_at)->format('Y-m-d H:i'),
        ];
    }
}
