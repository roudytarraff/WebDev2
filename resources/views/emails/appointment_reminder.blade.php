<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #d97706; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 28px 32px; color: #374151; line-height: 1.6; }
        .detail { background: #fffbeb; border-radius: 6px; padding: 16px; margin: 16px 0; border-left: 4px solid #d97706; }
        .detail p { margin: 6px 0; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Reminder: Appointment Tomorrow</h1>
    </div>
    <div class="body">
        <p>Hello {{ $appointment->citizen->full_name ?? 'Valued Citizen' }},</p>
        <p>This is a friendly reminder that you have an appointment <strong>tomorrow</strong>.</p>
        <div class="detail">
            <p><strong>Request #:</strong> {{ $appointment->request->request_number ?? '' }}</p>
            <p><strong>Service:</strong> {{ $appointment->request->service->name ?? '' }}</p>
            <p><strong>Office:</strong> {{ $appointment->office->name ?? '' }}</p>
            <p><strong>Date:</strong> {{ $appointment->slot->slot_date ?? '' }}</p>
            <p><strong>Time:</strong> {{ $appointment->slot->start_time ?? '' }} – {{ $appointment->slot->end_time ?? '' }}</p>
        </div>
        <p>Please remember to bring all required documents. If you need to reschedule, contact the office as soon as possible.</p>
    </div>
    <div class="footer">
        <p>This is an automated message from the E-Services Portal. Please do not reply.</p>
    </div>
</div>
</body>
</html>
