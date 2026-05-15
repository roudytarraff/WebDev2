<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #2563eb; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 28px 32px; color: #374151; line-height: 1.6; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; background: #dbeafe; color: #1d4ed8; font-weight: 600; text-transform: capitalize; margin: 12px 0; }
        .detail { background: #f9fafb; border-radius: 6px; padding: 16px; margin: 16px 0; }
        .detail p { margin: 4px 0; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Request Status Update</h1>
    </div>
    <div class="body">
        <p>Hello {{ $serviceRequest->citizen->full_name ?? 'Valued Citizen' }},</p>
        <p>Your service request status has been updated.</p>
        <div class="detail">
            <p><strong>Request #:</strong> {{ $serviceRequest->request_number }}</p>
            <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? '' }}</p>
            <p><strong>New Status:</strong></p>
            <span class="status-badge">{{ str_replace('_', ' ', $serviceRequest->status) }}</span>
        </div>
        @if($serviceRequest->statusHistory->last()?->note)
            <p><strong>Note from staff:</strong><br>{{ $serviceRequest->statusHistory->last()->note }}</p>
        @endif
        <p>You can track your request at any time using the link below.</p>
        <p><a href="{{ route('tracking.show', $serviceRequest->qr_code ?? $serviceRequest->request_number) }}">Track Your Request</a></p>
    </div>
    <div class="footer">
        <p>This is an automated message from the E-Services Portal. Please do not reply.</p>
    </div>
</div>
</body>
</html>
