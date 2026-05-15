<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #7c3aed; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 28px 32px; color: #374151; line-height: 1.6; }
        .amount { font-size: 32px; font-weight: 700; color: #7c3aed; margin: 12px 0; }
        .detail { background: #f5f3ff; border-radius: 6px; padding: 16px; margin: 16px 0; }
        .detail p { margin: 6px 0; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Payment Receipt</h1>
    </div>
    <div class="body">
        <p>Hello {{ $payment->user->full_name ?? 'Valued Citizen' }},</p>
        <p>Your payment has been successfully processed.</p>
        <div class="amount">{{ strtoupper($payment->currency ?? 'USD') }} {{ number_format($payment->amount, 2) }}</div>
        <div class="detail">
            <p><strong>Request #:</strong> {{ $payment->request->request_number ?? '' }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method ?? '') }}</p>
            <p><strong>Reference:</strong> {{ $payment->transaction_reference ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
            <p><strong>Date:</strong> {{ optional($payment->paid_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i') }}</p>
        </div>
        <p>Please keep this receipt for your records.</p>
    </div>
    <div class="footer">
        <p>This is an automated message from the E-Services Portal. Please do not reply.</p>
    </div>
</div>
</body>
</html>
