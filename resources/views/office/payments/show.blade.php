<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
@include('office.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Payment Details</h1>
            <p>{{ Auth::user()->full_name ?? '' }}</p>
        </div>
    </header>

    <div class="back-row">
        <a href="{{ route('office.payments.index') }}" class="button secondary">Back</a>

        @if($payment->status === 'success')
            <a href="{{ route('office.payments.receipt', $payment->id) }}" class="button">
                Download Receipt
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
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
            <h2>Payment</h2>

            <p><strong>Receipt Number:</strong> RCPT-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Request:</strong> {{ $payment->request->request_number ?? '' }}</p>
            <p><strong>Citizen:</strong> {{ $payment->request->citizen->full_name ?? '' }}</p>
            <p><strong>Amount:</strong> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
            <p><strong>Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
            <p><strong>Provider:</strong> {{ $payment->provider ?? 'N/A' }}</p>
            <p><strong>Reference:</strong> {{ $payment->transaction_reference ?? 'No reference' }}</p>
            <p><strong>Status:</strong> <span class="badge">{{ ucfirst($payment->status) }}</span></p>

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
            <h2>Receipt</h2>

            @if($payment->status === 'success')
                <p>This payment is successful. Receipt can be downloaded.</p>

                <a href="{{ route('office.payments.receipt', $payment->id) }}" class="button">
                    Download Receipt PDF
                </a>
            @elseif($payment->status === 'pending')
                <p>This payment is pending. Receipt is not available yet.</p>
            @else
                <p>This payment failed. Receipt is not available.</p>
            @endif
        </div>
    </section>

    <div class="panel">
        <h2>Transactions</h2>

        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Reference</th>
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
                        <td colspan="5">No transactions.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>