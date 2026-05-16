<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Payment Details</h1>
            <p>{{ $payment->request->request_number ?? 'Payment #' . $payment->id }}</p>
        </div>
    </header>

    <div class="back-row">
        <a href="{{ route('citizen.payments.index') }}" class="button secondary">Back to Payment History</a>

        @if($payment->request)
            <a href="{{ route('citizen.requests.show', $payment->request_id) }}" class="button secondary">
                View Request
            </a>
        @endif

        @if($payment->status === 'success')
            <a href="{{ route('citizen.payments.receipt.download', $payment->id) }}" class="button">
                Download Receipt
            </a>
        @endif
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

    <section class="grid two">
        <div class="panel">
            <h2>Payment Summary</h2>

            <p><strong>Receipt Number:</strong> RCPT-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Amount:</strong> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
            <p><strong>Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
            <p><strong>Provider:</strong> {{ $payment->provider ?? 'N/A' }}</p>
            <p><strong>Status:</strong> <span class="badge">{{ ucfirst($payment->status) }}</span></p>
            <p><strong>Reference:</strong> {{ $payment->transaction_reference ?? 'No reference' }}</p>

            <p>
                <strong>Paid At:</strong>
                @if($payment->paid_at)
                    {{ $payment->paid_at->format('M d, Y h:i A') }}
                @else
                    Not paid yet
                @endif
            </p>
        </div>

        <div class="panel">
            <h2>Request Information</h2>

            <p><strong>Request Number:</strong> {{ $payment->request->request_number ?? 'No request' }}</p>
            <p><strong>Service:</strong> {{ $payment->request->service->name ?? 'Service unavailable' }}</p>
            <p><strong>Office:</strong> {{ $payment->request->office->name ?? 'Office unavailable' }}</p>
            <p><strong>Municipality:</strong> {{ $payment->request->office->municipality->name ?? 'No municipality' }}</p>

            @if($payment->request)
                <p>
                    <strong>Request Status:</strong>
                    <span class="badge">
                        {{ ucwords(str_replace('_', ' ', $payment->request->status)) }}
                    </span>
                </p>
            @endif
        </div>
    </section>

    <section class="grid two">
        <div class="panel">
            <h2>Citizen Information</h2>

            <p><strong>Name:</strong> {{ $payment->user->full_name ?? '' }}</p>
            <p><strong>Email:</strong> {{ $payment->user->email ?? 'No email' }}</p>
        </div>

        <div class="panel">
            <h2>Receipt Status</h2>

            @if($payment->status === 'success')
                <p>This payment is successful. A receipt is available for download.</p>

                <a href="{{ route('citizen.payments.receipt.download', $payment->id) }}" class="button">
                    Download Receipt PDF
                </a>
            @elseif($payment->status === 'pending')
                <p>This payment is still pending. Receipt will be available after successful payment.</p>
            @else
                <p>This payment failed. Receipt is not available.</p>
            @endif
        </div>
    </section>

    <div class="panel">
        <div class="panel-head">
            <h2>Transactions</h2>
            <span class="badge">{{ $payment->transactions->count() }} records</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Provider Reference</th>
                    <th>Hash</th>
                    <th>Status</th>
                    <th>Processed At</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payment->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_type }}</td>
                        <td>{{ $transaction->provider_reference ?? 'No provider reference' }}</td>
                        <td>{{ $transaction->tx_hash ?? 'N/A' }}</td>
                        <td><span class="badge">{{ ucfirst($transaction->status) }}</span></td>
                        <td>
                            @if($transaction->processed_at)
                                {{ $transaction->processed_at->format('M d, Y h:i A') }}
                            @else
                                Not processed yet
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>