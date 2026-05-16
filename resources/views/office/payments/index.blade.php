<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
@include('office.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Payments</h1>
            <p>{{ Auth::user()->full_name ?? '' }}</p>
        </div>
    </header>

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

    <div class="stats-grid">
        <div class="stat">
            <span>Total Revenue</span>
            <strong>${{ number_format((float) $totalRevenue, 2) }}</strong>
        </div>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->request->request_number ?? '' }}</td>
                        <td>{{ $payment->user->full_name ?? '' }}</td>
                        <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>{{ $payment->provider ?? 'N/A' }}</td>
                        <td>
                            <span class="badge">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td>
                            @if($payment->paid_at)
                                {{ $payment->paid_at->format('M d, Y h:i A') }}
                            @else
                                Not paid yet
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('office.payments.show', $payment->id) }}">View</a>

                            @if($payment->status === 'success')
                                |
                                <a href="{{ route('office.payments.receipt', $payment->id) }}">
                                    Receipt
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>