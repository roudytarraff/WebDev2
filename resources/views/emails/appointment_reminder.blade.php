@component('mail::message')
# Appointment Reminder

Hello {{ $appointment->citizen->full_name ?? 'Citizen' }},

This is a reminder for your upcoming appointment.

@php
    $appointmentDateTime = $appointment->appointmentDateTime();
@endphp

**Request Number:** {{ $appointment->request->request_number ?? 'N/A' }}

**Service:** {{ $appointment->request->service->name ?? 'Service unavailable' }}

**Office:** {{ $appointment->office->name ?? 'Office unavailable' }}

**Municipality:** {{ $appointment->office->municipality->name ?? 'Municipality unavailable' }}

@if($appointmentDateTime)
**Date:** {{ $appointmentDateTime->format('M d, Y') }}

**Time:** {{ $appointmentDateTime->format('h:i A') }}
@endif

@if($appointment->office->contact_phone)
**Office Phone:** {{ $appointment->office->contact_phone }}
@endif

@if($appointment->office->contact_email)
**Office Email:** {{ $appointment->office->contact_email }}
@endif

Please arrive on time and bring all required documents.

@component('mail::button', ['url' => route('citizen.requests.show', $appointment->request_id)])
View Request
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent