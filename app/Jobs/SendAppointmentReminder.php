<?php

namespace App\Jobs;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Appointment $appointment) {}

    public function handle(SmsService $sms): void
    {
        $citizen = $this->appointment->citizen;

        if (! $citizen) {
            return;
        }

        // Email reminder
        Mail::to($citizen->email)->queue(new AppointmentReminderMail($this->appointment));

        // SMS reminder if phone available
        if ($citizen->phone) {
            $date = $this->appointment->slot->slot_date ?? 'tomorrow';
            $time = $this->appointment->slot->start_time ?? '';
            $sms->send(
                $citizen->phone,
                "Reminder: You have an appointment on {$date} at {$time}. " .
                "Office: " . ($this->appointment->office->name ?? '') . ".",
                'appointment_reminder'
            );
        }
    }
}
