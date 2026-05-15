<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

// User's own private notification channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private chat channel — accessible by the citizen or any staff of the chat's office
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (! $chat) {
        return false;
    }
    if ((int) $user->id === (int) $chat->citizen_user_id) {
        return true;
    }
    return $user->officeStaff()->where('office_id', $chat->office_id)->where('status', 'active')->exists();
});

// Office staff private channel — new requests and document uploads
Broadcast::channel('office.{officeId}', function ($user, $officeId) {
    return $user->officeStaff()->where('office_id', $officeId)->where('status', 'active')->exists();
});
