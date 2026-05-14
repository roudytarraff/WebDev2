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
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div class="stats-grid">
                <div class="stat">
                    <span>Total Revenue</span>
                    <strong>${{ number_format($totalRevenue, 2) }}</strong>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($payments as $payment)<tr>
                        <td>{{ $payment->request->request_number ?? '' }}</td>
                        <td>{{ $payment->user->full_name ?? '' }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>
                            <span class="badge">{{ $payment->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('office.payments.show', $payment->id) }}">View</a>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="6">No payments yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>