<?php

namespace App\Http\Controllers\Office;

use App\Events\NotificationSent;
use App\Jobs\SendAppointmentReminder;
use App\Mail\AppointmentConfirmationMail;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\FcmService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OfficeAppointmentController extends OfficeBaseController
{
    public function index()
    {
        $office       = $this->currentOffice();
        $appointments = Appointment::with(['citizen', 'request.service', 'slot'])
            ->where('office_id', $office->id)
            ->latest()
            ->get();

        return view('office.appointments.index', compact('office', 'appointments'));
    }

    public function show(string $id)
    {
        $office      = $this->currentOffice();
        $appointment = Appointment::with(['citizen', 'request.service', 'slot'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.appointments.show', compact('appointment'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
            'notes'  => 'nullable|string',
        ]);

        $office      = $this->currentOffice();
        $appointment = Appointment::with(['citizen', 'request.service', 'slot', 'office'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        $appointment->status = $request->status;
        $appointment->notes  = $request->notes;
        $appointment->save();

        $citizen      = $appointment->citizen;
        $statusLabel  = ucfirst($request->status);

        $notification = Notification::create([
            'user_id'  => $citizen->id,
            'type'     => 'appointment',
            'title'    => 'Appointment updated',
            'message'  => "Your appointment status is now {$statusLabel}.",
            'channel'  => 'system',
            'is_read'  => false,
        ]);

        broadcast(new NotificationSent($notification));

        if ($request->status === 'scheduled') {
            // Email confirmation
            if ($citizen->email) {
                Mail::to($citizen->email)->queue(new AppointmentConfirmationMail($appointment));
            }

            // SMS confirmation
            if ($citizen->phone) {
                $date = $appointment->slot->slot_date ?? '';
                $time = $appointment->slot->start_time ?? '';
                app(SmsService::class)->send(
                    $citizen->phone,
                    "Your appointment is confirmed for {$date} at {$time}. Office: " . ($appointment->office->name ?? '') . '.',
                    'appointment_confirmed',
                    $citizen->id
                );
            }

            // Schedule 24-hour reminder job (fires 24h before appointment slot date)
            if ($appointment->slot?->slot_date) {
                $reminderTime = \Carbon\Carbon::parse($appointment->slot->slot_date . ' ' . ($appointment->slot->start_time ?? '09:00'))->subDay();
                if ($reminderTime->isFuture()) {
                    SendAppointmentReminder::dispatch($appointment)->delay($reminderTime);
                }
            }
        }

        // FCM push
        app(FcmService::class)->notifyUser(
            $citizen,
            'Appointment Updated',
            "Your appointment is now {$statusLabel}."
        );

        return back()->with('success', 'Appointment updated successfully.');
    }
}
