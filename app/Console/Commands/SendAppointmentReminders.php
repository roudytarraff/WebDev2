<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Notification;
use App\Services\AppointmentSmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send email and SMS reminders for appointments happening within the next 24 hours.';

    private const REMINDER_TYPE = '24_hours_before';

    public function handle(AppointmentSmsService $smsService): int
    {
        $now = now();
        $windowEnd = now()->addHours(24);

        $emailSent = 0;
        $smsSent = 0;
        $skipped = 0;
        $failed = 0;

        $appointments = Appointment::with([
                'citizen',
                'request.service',
                'office.municipality',
                'slot',
                'reminders',
            ])
            ->where('status', 'scheduled')
            ->whereHas('slot', function ($query) use ($now, $windowEnd) {
                $query->whereBetween('slot_date', [
                    $now->toDateString(),
                    $windowEnd->toDateString(),
                ]);
            })
            ->get()
            ->filter(function (Appointment $appointment) use ($now, $windowEnd) {
                $appointmentDateTime = $appointment->appointmentDateTime();

                if (! $appointmentDateTime) {
                    return false;
                }

                return $appointmentDateTime->between($now, $windowEnd, true);
            });

        foreach ($appointments as $appointment) {
            $citizen = $appointment->citizen;

            if (! $citizen) {
                $skipped++;
                continue;
            }

            if ($citizen->email) {
                if ($this->reminderExists($appointment, 'email')) {
                    $skipped++;
                } else {
                    try {
                        Mail::to($citizen->email)->send(new AppointmentReminderMail($appointment));

                        $this->recordReminder($appointment, 'email', 'sent');
                        $this->createNotification($appointment, 'email');

                        $emailSent++;
                    } catch (\Throwable $exception) {
                        $this->recordReminder($appointment, 'email', 'failed', $exception->getMessage());

                        $this->error('Email reminder failed for appointment #' . $appointment->id . ': ' . $exception->getMessage());

                        $failed++;
                    }
                }
            } else {
                $skipped++;
            }

            if ($citizen->phone) {
                if ($this->reminderExists($appointment, 'sms')) {
                    $skipped++;
                } else {
                    try {
                        $smsService->send($appointment);

                        $this->recordReminder($appointment, 'sms', 'sent');
                        $this->createNotification($appointment, 'sms');

                        $smsSent++;
                    } catch (\Throwable $exception) {
                        $this->recordReminder($appointment, 'sms', 'failed', $exception->getMessage());

                        $this->error('SMS reminder failed for appointment #' . $appointment->id . ': ' . $exception->getMessage());

                        $failed++;
                    }
                }
            } else {
                $skipped++;
            }
        }

        $this->info('Appointment reminders processed.');
        $this->info('Appointments found: ' . $appointments->count());
        $this->info('Email reminders sent: ' . $emailSent);
        $this->info('SMS reminders sent: ' . $smsSent);
        $this->info('Skipped reminders: ' . $skipped);
        $this->info('Failed reminders: ' . $failed);

        return self::SUCCESS;
    }

    private function reminderExists(Appointment $appointment, string $channel): bool
    {
        return AppointmentReminder::where('appointment_id', $appointment->id)
            ->where('channel', $channel)
            ->where('reminder_type', self::REMINDER_TYPE)
            ->exists();
    }

    private function recordReminder(
        Appointment $appointment,
        string $channel,
        string $status,
        ?string $errorMessage = null
    ): void {
        AppointmentReminder::create([
            'appointment_id' => $appointment->id,
            'channel' => $channel,
            'reminder_type' => self::REMINDER_TYPE,
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
            'error_message' => $errorMessage,
        ]);
    }

    private function createNotification(Appointment $appointment, string $channel): void
    {
        $appointmentDateTime = $appointment->appointmentDateTime();

        $dateText = $appointmentDateTime
            ? $appointmentDateTime->format('M d, Y h:i A')
            : 'your appointment date';

        $serviceName = $appointment->request->service->name ?? 'your service';
        $requestNumber = $appointment->request->request_number ?? 'N/A';

        Notification::create([
            'user_id' => $appointment->citizen_user_id,
            'type' => 'appointment_reminder',
            'title' => 'Appointment reminder',
            'message' => "Reminder for your appointment {$requestNumber} for {$serviceName} on {$dateText}.",
            'channel' => $channel,
            'is_read' => false,
        ]);
    }
}