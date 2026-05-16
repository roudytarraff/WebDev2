<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class AppointmentSmsService
{
    public function send(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'citizen',
            'request.service',
            'office',
            'slot',
        ]);

        $citizen = $appointment->citizen;

        if (! $citizen || ! $citizen->phone) {
            throw new \Exception('Citizen phone number is missing.');
        }

        $message = $this->buildMessage($appointment);

        $driver = config('services.sms.driver', 'log');

        if ($driver === 'log') {
            Log::info('Appointment SMS reminder sent in log mode.', [
                'to' => $citizen->phone,
                'message' => $message,
            ]);

            return;
        }

        throw new \Exception('SMS driver [' . $driver . '] is not implemented yet.');
    }

    public function buildMessage(Appointment $appointment): string
    {
        $appointmentDateTime = $appointment->appointmentDateTime();

        $serviceName = $appointment->request->service->name ?? 'your service';
        $officeName = $appointment->office->name ?? 'the office';
        $requestNumber = $appointment->request->request_number ?? 'N/A';

        $date = $appointmentDateTime
            ? $appointmentDateTime->format('M d, Y')
            : 'your appointment date';

        $time = $appointmentDateTime
            ? $appointmentDateTime->format('h:i A')
            : 'your appointment time';

        return "Reminder: Your appointment for {$serviceName} is on {$date} at {$time} at {$officeName}. Request: {$requestNumber}.";
    }
}