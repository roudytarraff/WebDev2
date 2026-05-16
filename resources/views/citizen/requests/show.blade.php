<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .status-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .status-step {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .status-step.active {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }

        .status-step.rejected {
            border-color: #dc2626;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 700;
        }

        .details-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .appointment-action-form {
            margin: 0;
        }

        .appointment-cancel-button {
            width: auto;
            padding: 8px 12px;
            font-size: 14px;
            white-space: nowrap;
        }

        .appointment-warning {
            display: block;
            max-width: 220px;
            font-size: 13px;
            line-height: 1.4;
        }

        .message {
            border-left: 3px solid #2563eb;
            background: #f8fafc;
            padding: 12px;
            margin-bottom: 10px;
        }

        .qr-box {
            margin: 16px 0;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .qr-image {
            width: 180px;
            height: 180px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            background: #ffffff;
            object-fit: contain;
        }

        .qr-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .qr-actions .button,
        .qr-actions .button.secondary {
            width: auto;
        }

        .payment-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            background: #ffffff;
        }

        .payment-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .payment-card-header h3 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .payment-item {
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px;
        }

        .payment-item span {
            display: block;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .payment-item strong {
            display: block;
            font-size: 14px;
            color: #0f172a;
            word-break: break-word;
        }

        .payment-reference {
            font-size: 12px;
            line-height: 1.5;
            word-break: break-all;
        }

        .payment-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .payment-actions .button,
        .payment-actions .button.secondary {
            width: auto;
            text-decoration: none;
        }

        .feedback-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
        }

        .feedback-rating {
            color: #f59e0b;
            font-size: 20px;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .feedback-reply {
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        @media (max-width: 900px) {
            .appointment-warning {
                max-width: 100%;
            }

            .qr-actions .button,
            .qr-actions .button.secondary {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 700px) {
            .payment-card-header {
                flex-direction: column;
            }

            .payment-grid {
                grid-template-columns: 1fr;
            }

            .payment-actions .button,
            .payment-actions .button.secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Request Details</h1>
            <p>{{ $serviceRequest->request_number }}</p>
        </div>
    </header>

    <div class="back-row">
        <a href="{{ route('citizen.requests.index') }}" class="button secondary">
            Back to My Requests
        </a>
    </div>

    @if(session('success'))
        <div class="alert success">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php
        $normalSteps = ['pending', 'in_progress', 'approved', 'completed'];
        $currentStatus = $serviceRequest->status;

        $hasCompletedAppointment = $serviceRequest->appointments->contains(function ($appointment) {
            return $appointment->status === 'completed';
        });

        $canLeaveFeedback = $serviceRequest->status === 'completed' || $hasCompletedAppointment;
    @endphp

    <section class="grid two">
        <div class="panel">
            <h2>Request Summary</h2>

            <p><strong>Request Number:</strong> {{ $serviceRequest->request_number }}</p>

            <p>
                <strong>Status:</strong>
                <span class="badge">{{ ucwords(str_replace('_', ' ', $serviceRequest->status)) }}</span>
            </p>

            <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'Service unavailable' }}</p>
            <p><strong>Category:</strong> {{ $serviceRequest->service->category->name ?? 'No category' }}</p>
            <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'Office unavailable' }}</p>
            <p><strong>Municipality:</strong> {{ $serviceRequest->office->municipality->name ?? 'No municipality' }}</p>
            <p><strong>Assigned To:</strong> {{ $serviceRequest->assignedTo->full_name ?? 'Not assigned yet' }}</p>

            <p>
                <strong>Submitted:</strong>
                @if($serviceRequest->submitted_at)
                    {{ \Illuminate\Support\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
                @else
                    Not submitted
                @endif
            </p>

            <p><strong>Description:</strong></p>
            <p>{{ $serviceRequest->description ?? 'No description provided.' }}</p>
        </div>

        <div class="panel">
            <h2>Service Information</h2>

            <p><strong>Price:</strong> ${{ number_format((float) ($serviceRequest->service->price ?? 0), 2) }}</p>
            <p><strong>Duration:</strong> {{ $serviceRequest->service->duration_minutes ?? 0 }} minutes</p>
            <p><strong>Requires Appointment:</strong> {{ $serviceRequest->service?->requires_appointment ? 'Yes' : 'No' }}</p>
            <p><strong>Online Payment:</strong> {{ $serviceRequest->service?->supports_online_payment ? 'Supported' : 'Not supported' }}</p>
            <p><strong>Crypto Payment:</strong> {{ $serviceRequest->service?->supports_crypto_payment ? 'Supported' : 'Not supported' }}</p>

            @if($serviceRequest->office?->address)
                <p>
                    <strong>Office Address:</strong>
                    {{ $serviceRequest->office->address->address_line_1 }},
                    {{ $serviceRequest->office->address->city }},
                    {{ $serviceRequest->office->address->region }}
                </p>
            @endif
        </div>
    </section>

    <div class="panel">
        <h2>Tracking Progress</h2>

        @if($currentStatus === 'rejected')
            <div class="status-steps">
                <div class="status-step active">Pending</div>
                <div class="status-step rejected">Rejected</div>
            </div>
        @else
            <div class="status-steps">
                @foreach($normalSteps as $step)
                    <div class="status-step {{ $currentStatus === $step ? 'active' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $step)) }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <section class="grid two">
        <div class="panel">
            <h2>Status History</h2>

            @forelse($serviceRequest->statusHistory->sortByDesc('changed_at') as $history)
                <div class="message">
                    <strong>
                        @if($history->old_status)
                            {{ ucwords(str_replace('_', ' ', $history->old_status)) }}
                        @else
                            Request Created
                        @endif
                        →
                        {{ ucwords(str_replace('_', ' ', $history->new_status)) }}
                    </strong>

                    <br>

                    @if($history->note)
                        {{ $history->note }}
                    @else
                        No note provided.
                    @endif

                    <br>

                    <small>
                        By {{ $history->changedBy->full_name ?? 'System' }}
                        -
                        @if($history->changed_at)
                            {{ \Illuminate\Support\Carbon::parse($history->changed_at)->format('M d, Y h:i A') }}
                        @else
                            No date
                        @endif
                    </small>
                </div>
            @empty
                <p>No status history yet.</p>
            @endforelse
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>QR / Public Tracking</h2>
                <span class="badge">QR Code</span>
            </div>

            @php
                $qrService = app(\App\Services\RequestQrCodeService::class);

                if ($serviceRequest->qr_code && ! $qrService->exists($serviceRequest)) {
                    $qrService->generate($serviceRequest);
                }
            @endphp

            @if($serviceRequest->qr_code)
                <p class="muted">
                    Scan this QR code to open the public tracking page for this request.
                    This page can be accessed without logging in.
                </p>

                <div class="qr-box">
                    <img
                        src="{{ route('tracking.qr-image', $serviceRequest->qr_code) }}"
                        alt="Request QR Code"
                        class="qr-image"
                    >
                </div>

                <p><strong>QR Code:</strong> {{ $serviceRequest->qr_code }}</p>

                <div class="qr-actions">
                    <a href="{{ route('tracking.show', $serviceRequest->qr_code) }}" target="_blank" class="button">
                        Open Public Tracking Page
                    </a>

                    <a href="{{ route('tracking.qr-image', $serviceRequest->qr_code) }}" target="_blank" class="button secondary">
                        Open QR Image
                    </a>
                </div>
            @else
                <p class="muted">
                    No QR code has been generated for this request yet.
                </p>
            @endif
        </div>
    </section>

    <section class="grid two">
        <div class="panel">
            <div class="panel-head">
                <h2>Uploaded Documents</h2>
                <span class="badge">{{ $serviceRequest->documents->count() }} documents</span>
            </div>

            <div class="details-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Uploaded By</th>
                            <th>Role</th>
                            <th>Uploaded At</th>
                            <th>File</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceRequest->documents as $document)
                            <tr>
                                <td>{{ $document->requiredDocument->document_name ?? $document->file_name }}</td>
                                <td>{{ $document->uploadedBy->full_name ?? 'Unknown' }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $document->document_role)) }}</td>
                                <td>
                                    @if($document->uploaded_at)
                                        {{ \Illuminate\Support\Carbon::parse($document->uploaded_at)->format('M d, Y h:i A') }}
                                    @else
                                        No date
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('citizen.requests.documents.download', [$serviceRequest->id, $document->id]) }}">
                                        Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No uploaded documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Generated Documents</h2>
                <span class="badge">{{ $serviceRequest->generatedDocuments->count() }} files</span>
            </div>

            <div class="details-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Generated At</th>
                            <th>File</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceRequest->generatedDocuments as $document)
                            <tr>
                                <td>{{ ucfirst($document->document_type) }}</td>
                                <td>
                                    @if($document->generated_at)
                                        {{ \Illuminate\Support\Carbon::parse($document->generated_at)->format('M d, Y h:i A') }}
                                    @else
                                        No date
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('citizen.requests.generated-documents.download', [$serviceRequest->id, $document->id]) }}">
                                        Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No generated documents yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-head">
            <h2>Feedback</h2>

            @if($serviceRequest->feedback)
                <span class="badge">Submitted</span>
            @elseif($canLeaveFeedback)
                <span class="badge">Available</span>
            @else
                <span class="badge">Not available yet</span>
            @endif
        </div>

        @if($serviceRequest->feedback)
            <div class="feedback-box">
                <h3>Your Feedback</h3>

                <div class="feedback-rating">
                    {{ str_repeat('★', $serviceRequest->feedback->rating) }}
                </div>

                <p><strong>Rating:</strong> {{ $serviceRequest->feedback->rating }}/5</p>

                <p><strong>Your Comment:</strong></p>
                <p>{{ $serviceRequest->feedback->comment ?: 'No comment provided.' }}</p>

                <small class="muted">
                    Submitted on {{ $serviceRequest->feedback->created_at->format('M d, Y h:i A') }}
                </small>

                @if($serviceRequest->feedback->office_reply)
                    <div class="feedback-reply">
                        <strong>Office Reply:</strong>
                        <p>{{ $serviceRequest->feedback->office_reply }}</p>
                    </div>
                @else
                    <p class="muted">The office has not replied yet.</p>
                @endif
            </div>
        @elseif($canLeaveFeedback)
            <p>
                This service request or appointment is completed.
                You can now rate your experience.
            </p>

            <a href="{{ route('citizen.feedback.create', $serviceRequest->id) }}" class="button">
                Leave Feedback
            </a>
        @else
            <p class="muted">
                Feedback will be available after the request or appointment is completed.
            </p>
        @endif
    </div>

    <section class="grid two">
        <div class="panel">
            <div class="panel-head">
                <h2>Payments</h2>
                <span class="badge">{{ $serviceRequest->payments->count() }} payments</span>
            </div>

            @forelse($serviceRequest->payments as $payment)
                <div class="payment-card">
                    <div class="payment-card-header">
                        <div>
                            <h3>
                                {{ $payment->currency }}
                                {{ number_format((float) $payment->amount, 2) }}
                            </h3>

                            <small class="muted">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                @if($payment->provider)
                                    via {{ ucfirst($payment->provider) }}
                                @endif
                            </small>
                        </div>

                        <span class="badge">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>

                    <div class="payment-grid">
                        <div class="payment-item">
                            <span>Payment Method</span>
                            <strong>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</strong>
                        </div>

                        <div class="payment-item">
                            <span>Provider</span>
                            <strong>{{ $payment->provider ? ucfirst($payment->provider) : 'N/A' }}</strong>
                        </div>

                        <div class="payment-item">
                            <span>Paid At</span>
                            <strong>
                                @if($payment->paid_at)
                                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('M d, Y h:i A') }}
                                @else
                                    Not paid yet
                                @endif
                            </strong>
                        </div>

                        <div class="payment-item">
                            <span>Receipt</span>
                            <strong>
                                @if($payment->status === 'success')
                                    Available
                                @else
                                    Not available yet
                                @endif
                            </strong>
                        </div>

                        <div class="payment-item" style="grid-column: 1 / -1;">
                            <span>Transaction Reference</span>

                            <strong class="payment-reference">
                                {{ $payment->transaction_reference ?? 'No reference' }}
                            </strong>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <a href="{{ route('citizen.payments.show', $payment->id) }}" class="button secondary">
                            View Payment
                        </a>

                        @if($payment->status === 'success')
                            <a href="{{ route('citizen.payments.receipt.download', $payment->id) }}" class="button">
                                Download Receipt
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p>No payments found for this request.</p>
            @endforelse
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Appointments</h2>
                <span class="badge">{{ $serviceRequest->appointments->count() }} appointments</span>
            </div>

            <div class="details-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceRequest->appointments as $appointment)
                            @php
                                $slot = $appointment->slot;
                                $canCancel = false;

                                if ($slot && $appointment->status !== 'cancelled') {
                                    $appointmentDateTime = \Illuminate\Support\Carbon::parse($slot->slot_date . ' ' . $slot->start_time);
                                    $canCancel = now()->diffInHours($appointmentDateTime, false) >= 24;
                                }
                            @endphp

                            <tr>
                                <td>
                                    @if($slot)
                                        {{ \Illuminate\Support\Carbon::parse($slot->slot_date)->format('M d, Y') }}
                                    @else
                                        No date
                                    @endif
                                </td>

                                <td>
                                    @if($slot)
                                        {{ substr($slot->start_time, 0, 5) }}
                                        -
                                        {{ substr($slot->end_time, 0, 5) }}
                                    @else
                                        No time
                                    @endif
                                </td>

                                <td>
                                    <span class="badge">{{ ucfirst($appointment->status) }}</span>
                                </td>

                                <td>{{ $appointment->notes ?? 'No notes' }}</td>

                                <td>
                                    @if($canCancel)
                                        <form
                                            method="POST"
                                            action="{{ route('citizen.appointments.cancel', $appointment->id) }}"
                                            class="appointment-action-form"
                                            onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                                        >
                                            @csrf

                                            <button type="submit" class="button secondary appointment-cancel-button">
                                                Cancel Appointment
                                            </button>
                                        </form>
                                    @elseif($appointment->status === 'cancelled')
                                        <span class="muted">Cancelled</span>
                                    @else
                                        <span class="muted appointment-warning">
                                            Cannot cancel less than 24 hours before appointment.
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No appointments found for this request.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
</body>
</html>