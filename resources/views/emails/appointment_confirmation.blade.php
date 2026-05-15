<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #16a34a; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 28px 32px; color: #374151; line-height: 1.6; }
        .detail { background: #f0fdf4; border-radius: 6px; padding: 16px; margin: 16px 0; border-left: 4px solid #16a34a; }
        .detail p { margin: 6px 0; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Appointment Confirmed</h1>
    </div>
    <div class="body">
        <p>Hello {{ $appointment->citizen->full_name ?? 'Valued Citizen' }},</p>
        <p>Your appointment has been confirmed. Here are the details:</p>
        <div class="detail">
            <p><strong>Request #:</strong> {{ $appointment->request->request_number ?? '' }}</p>
            <p><strong>Service:</strong> {{ $appointment->request->service->name ?? '' }}</p>
            <p><strong>Office:</strong> {{ $appointment->office->name ?? '' }}</p>
            <p><strong>Date:</strong> {{ $appointment->slot->slot_date ?? '' }}</p>
            <p><strong>Time:</strong> {{ $appointment->slot->start_time ?? '' }} – {{ $appointment->slot->end_time ?? '' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($appointment->status) }}</p>
        </div>
        <p>Please arrive a few minutes early. Bring any required documents with you.</p>
    </div>
    <div class="footer">
        <p>This is an automated message from the E-Services Portal. Please do not reply.</p>
    </div>
</div>
</body>
</html>
