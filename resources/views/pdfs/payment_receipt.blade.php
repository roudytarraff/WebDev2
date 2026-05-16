<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 13px;
            line-height: 1.6;
        }

        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .muted {
            color: #6b7280;
        }

        .section-title {
            margin-top: 24px;
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 9px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            width: 35%;
        }

        .success {
            color: #065f46;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 40px;
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
            font-size: 12px;
            color: #6b7280;
        }

        .signature {
            margin-top: 60px;
            width: 260px;
            border-top: 1px solid #111827;
            padding-top: 8px;
        }
    </style>
</head>

<body>
@php
    $transaction = $payment->transactions->sortByDesc('processed_at')->first();
    $receiptNumber = 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
@endphp

<div class="header">
    <h1>Payment Receipt</h1>

    <p>
        {{ $payment->request->office->municipality->name ?? 'Municipality' }}
        -
        {{ $payment->request->office->name ?? 'Office' }}
    </p>

    <p class="muted">Generated at: {{ now()->format('M d, Y h:i A') }}</p>
</div>

<p>
    This document confirms that the payment below was completed successfully through the municipal e-services platform.
</p>

<div class="section-title">Receipt Information</div>

<table>
    <tr>
        <th>Receipt Number</th>
        <td>{{ $receiptNumber }}</td>
    </tr>

    <tr>
        <th>Payment Status</th>
        <td class="success">{{ ucfirst($payment->status) }}</td>
    </tr>

    <tr>
        <th>Payment Date</th>
        <td>
            @if($payment->paid_at)
                {{ $payment->paid_at->format('M d, Y h:i A') }}
            @else
                Not available
            @endif
        </td>
    </tr>
</table>

<div class="section-title">Payment Details</div>

<table>
    <tr>
        <th>Amount</th>
        <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
    </tr>

    <tr>
        <th>Payment Method</th>
        <td>{{ ucfirst($payment->payment_method) }}</td>
    </tr>

    <tr>
        <th>Provider</th>
        <td>{{ $payment->provider ?? 'N/A' }}</td>
    </tr>

    <tr>
        <th>Transaction Reference</th>
        <td>{{ $payment->transaction_reference ?? 'No reference' }}</td>
    </tr>

    <tr>
        <th>Provider Reference</th>
        <td>{{ $transaction->provider_reference ?? 'No provider reference' }}</td>
    </tr>

    <tr>
        <th>Transaction Type</th>
        <td>{{ $transaction->transaction_type ?? 'No transaction type' }}</td>
    </tr>
</table>

<div class="section-title">Request Details</div>

<table>
    <tr>
        <th>Request Number</th>
        <td>{{ $payment->request->request_number ?? 'No request number' }}</td>
    </tr>

    <tr>
        <th>Service</th>
        <td>{{ $payment->request->service->name ?? 'Service unavailable' }}</td>
    </tr>

    <tr>
        <th>Request Status</th>
        <td>{{ ucwords(str_replace('_', ' ', $payment->request->status ?? 'unknown')) }}</td>
    </tr>
</table>

<div class="section-title">Citizen Details</div>

<table>
    <tr>
        <th>Citizen Name</th>
        <td>{{ $payment->user->full_name ?? $payment->request->citizen->full_name ?? 'Citizen unavailable' }}</td>
    </tr>

    <tr>
        <th>Email</th>
        <td>{{ $payment->user->email ?? 'No email' }}</td>
    </tr>
</table>

<div class="signature">
    Authorized Office Signature
</div>

<div class="footer">
    This receipt was generated automatically by the municipal e-services platform.
    Please keep it as proof of payment.
</div>
</body>
</html>