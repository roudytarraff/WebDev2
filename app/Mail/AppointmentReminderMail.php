<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment->loadMissing([
            'citizen',
            'request.service',
            'office.municipality',
            'slot',
        ]);
    }

    public function envelope(): Envelope
    {
        $requestNumber = $this->appointment->request->request_number ?? 'Appointment';

        return new Envelope(
            subject: 'Appointment Reminder - ' . $requestNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment_reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}