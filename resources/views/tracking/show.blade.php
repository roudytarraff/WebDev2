<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Tracking</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        body {
            background: #f1f5f9;
        }

        .tracking-wrapper {
            max-width: 1050px;
            margin: 0 auto;
            padding: 24px;
        }

        .tracking-header {
            background: #111827;
            color: #ffffff;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .tracking-header h1 {
            margin: 0 0 8px;
            color: #ffffff;
        }

        .tracking-header p {
            margin: 0;
            color: #dbeafe;
        }

        .tracking-status-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 20px;
            text-align: center;
        }

        .tracking-status-label {
            color: #64748b;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .tracking-status-value {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #2563eb;
            border-radius: 999px;
            padding: 10px 20px;
            font-size: 22px;
            font-weight: 800;
        }

        .tracking-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .tracking-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
        }

        .tracking-panel h2 {
            margin-top: 0;
            margin-bottom: 16px;
        }

        .tracking-panel p {
            line-height: 1.5;
        }

        .tracking-timeline-item {
            border-left: 3px solid #2563eb;
            background: #f8fafc;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .tracking-muted {
            color: #64748b;
        }

        .tracking-footer {
            text-align: center;
            color: #64748b;
            margin-top: 20px;
            font-size: 14px;
        }

        @media (max-width: 850px) {
            .tracking-wrapper {
                padding: 14px;
            }

            .tracking-grid {
                grid-template-columns: 1fr;
            }

            .tracking-status-value {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <main class="tracking-wrapper">
        <header class="tracking-header">
            <h1>Request Tracking</h1>
            <p>Public tracking page. No login is required.</p>
        </header>

        <section class="tracking-status-card">
            <div class="tracking-status-label">
                Current Request Status
            </div>

            <div class="tracking-status-value">
                {{ ucwords(str_replace('_', ' ', $serviceRequest->status)) }}
            </div>

            <p style="margin-top: 14px;">
                <strong>Request Number:</strong> {{ $serviceRequest->request_number }}
            </p>
        </section>

        <section class="tracking-grid">
            <div class="tracking-panel">
                <h2>Request Information</h2>

                <p>
                    <strong>Service:</strong>
                    {{ $serviceRequest->service->name ?? 'Service unavailable' }}
                </p>

                <p>
                    <strong>Category:</strong>
                    {{ $serviceRequest->service->category->name ?? 'No category' }}
                </p>

                <p>
                    <strong>Submitted:</strong>
                    @if($serviceRequest->submitted_at)
                        {{ \Illuminate\Support\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
                    @else
                        Not available
                    @endif
                </p>

                <p>
                    <strong>QR Code:</strong>
                    {{ $serviceRequest->qr_code }}
                </p>
            </div>

            <div class="tracking-panel">
                <h2>Office Information</h2>

                <p>
                    <strong>Office:</strong>
                    {{ $serviceRequest->office->name ?? 'Office unavailable' }}
                </p>

                <p>
                    <strong>Municipality:</strong>
                    {{ $serviceRequest->office->municipality->name ?? 'No municipality' }}
                </p>

                @if($serviceRequest->office?->contact_email)
                    <p>
                        <strong>Email:</strong>
                        {{ $serviceRequest->office->contact_email }}
                    </p>
                @endif

                @if($serviceRequest->office?->contact_phone)
                    <p>
                        <strong>Phone:</strong>
                        {{ $serviceRequest->office->contact_phone }}
                    </p>
                @endif
            </div>
        </section>

        <section class="tracking-grid">
            <div class="tracking-panel">
                <h2>Appointment Details</h2>

                @forelse($serviceRequest->appointments as $appointment)
                    <p>
                        <strong>Status:</strong>
                        {{ ucfirst($appointment->status) }}
                    </p>

                    @if($appointment->slot)
                        <p>
                            <strong>Date:</strong>
                            {{ \Illuminate\Support\Carbon::parse($appointment->slot->slot_date)->format('M d, Y') }}
                        </p>

                        <p>
                            <strong>Time:</strong>
                            {{ substr($appointment->slot->start_time, 0, 5) }}
                            -
                            {{ substr($appointment->slot->end_time, 0, 5) }}
                        </p>
                    @else
                        <p class="tracking-muted">No appointment slot information available.</p>
                    @endif
                @empty
                    <p class="tracking-muted">
                        No appointment is linked to this request.
                    </p>
                @endforelse
            </div>

            <div class="tracking-panel">
                <h2>Payment Summary</h2>

                @forelse($serviceRequest->payments as $payment)
                    <p>
                        <strong>Amount:</strong>
                        {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                    </p>

                    <p>
                        <strong>Method:</strong>
                        {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                    </p>

                    <p>
                        <strong>Status:</strong>
                        {{ ucfirst($payment->status) }}
                    </p>
                @empty
                    <p class="tracking-muted">
                        No payment record is linked to this request.
                    </p>
                @endforelse
            </div>
        </section>

        <section class="tracking-panel">
            <h2>Status History</h2>

            @forelse($serviceRequest->statusHistory->sortByDesc('changed_at') as $history)
                <div class="tracking-timeline-item">
                    <strong>
                        {{ ucwords(str_replace('_', ' ', $history->old_status)) }}
                        →
                        {{ ucwords(str_replace('_', ' ', $history->new_status)) }}
                    </strong>

                    <br>

                    {{ $history->note ?? 'No note provided.' }}

                    <br>

                    <small class="tracking-muted">
                        @if($history->changed_at)
                            {{ \Illuminate\Support\Carbon::parse($history->changed_at)->format('M d, Y h:i A') }}
                        @else
                            No date available
                        @endif
                    </small>
                </div>
            @empty
                <p class="tracking-muted">
                    No status history is available yet.
                </p>
            @endforelse
        </section>

        <p class="tracking-footer">
            This is a public tracking page generated from the request QR code.
        </p>
    </main>
</body>
</html>