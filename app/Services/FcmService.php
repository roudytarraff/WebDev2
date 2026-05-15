<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $serverKey;
    private string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key', '');
    }

    /**
     * Send a push notification to a single FCM registration token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey) || str_starts_with($this->serverKey, 'your-')) {
            Log::info("FCM not configured. Push to {$token}: {$title} — {$body}");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type'  => 'application/json',
            ])->post($this->endpoint, [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => '/favicon.ico',
                    'click_action' => url('/'),
                ],
                'data' => $data,
            ]);

            if (! $response->successful()) {
                Log::error('FCM send failed: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to a User model (reads fcm_token from the user).
     */
    public function notifyUser(\App\Models\User $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->fcm_token)) {
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }
}
