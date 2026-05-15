<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Twilio\Rest\Client;

class SmsService
{
    private ?Client $client;

    public function __construct()
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');

        // Gracefully degrade when credentials are not configured
        $this->client = ($sid && $token && str_starts_with($sid, 'AC'))
            ? new Client($sid, $token)
            : null;
    }

    /**
     * Send an SMS and log it to the notifications table.
     *
     * @param  string  $to       E.164 phone number, e.g. +96170123456
     * @param  string  $body     Message body
     * @param  string  $type     Notification type (for logging / rate-limiting key)
     * @param  int|null $userId  Optional user_id for the notifications log
     */
    public function send(string $to, string $body, string $type = 'sms', ?int $userId = null): bool
    {
        $rateLimitKey = 'sms:' . $type . ':' . $to;

        // Max 5 SMS of the same type to same number per hour
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            Log::warning("SMS rate-limited: {$rateLimitKey}");
            return false;
        }

        RateLimiter::hit($rateLimitKey, 3600);

        try {
            if ($this->client) {
                $this->client->messages->create($to, [
                    'from' => config('services.twilio.from'),
                    'body' => $body,
                ]);
            } else {
                Log::info("SMS (Twilio not configured) to {$to}: {$body}");
            }

            if ($userId) {
                Notification::create([
                    'user_id'  => $userId,
                    'type'     => $type,
                    'title'    => 'SMS Sent',
                    'message'  => $body,
                    'channel'  => 'sms',
                    'is_read'  => true,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("SMS send failed to {$to}: " . $e->getMessage());
            return false;
        }
    }
}
