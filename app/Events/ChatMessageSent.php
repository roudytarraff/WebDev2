<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public ChatMessage $message)
    {
        $message->loadMissing('sender');

        $this->payload = [
            'id'             => $message->id,
            'chat_id'        => $message->chat_id,
            'sender_id'      => $message->sender_user_id,
            'sender_name'    => $message->sender->full_name ?? 'User',
            'message_text'   => $message->message_text,
            'attachment_url' => $message->attachment_path
                ? asset('storage/' . $message->attachment_path)
                : null,
            'sent_at' => optional($message->sent_at)->format('Y-m-d H:i'),
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->chat_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
