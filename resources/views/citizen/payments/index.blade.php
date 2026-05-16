<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Payment History</h1>
            <p>View your payments and download receipts.</p>
        </div>
    </header>

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

    <section class="stats-grid">
        <div class="stat">
            <span>Total Payments</span>
            <strong>{{ $totalPayments }}</strong>
        </div>

        <div class="stat">
            <span>Successful</span>
            <strong>{{ $successfulPayments }}</strong>
        </div>

        <div class="stat">
            <span>Pending</span>
            <strong>{{ $pendingPayments }}</strong>
        </div>

        <div class="stat">
            <span>Total Paid</span>
            <strong>USD {{ number_format((float) $totalPaid, 2) }}</strong>
        </div>
    </section>

    <div class="panel">
        <div class="panel-head">
            <h2>Filter Payments</h2>
            <span class="badge">{{ $payments->count() }} matching</span>
        </div>

        <form method="GET" action="{{ route('citizen.payments.index') }}">
            <div>
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by request number, service, office, or reference..."
                >
            </div>

            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($statusFilter === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Method</label>
                <select name="method">
                    <option value="">All Methods</option>
                    @foreach($methods as $method)
                        <option value="{{ $method }}" @selected($methodFilter === $method)>
                            {{ ucfirst($method) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('citizen.payments.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Payments</h2>
            <span class="badge">{{ $payments->count() }} records</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Service</th>
                    <th>Office</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <strong>{{ $payment->request->request_number ?? 'No request' }}</strong>
                        </td>

                        <td>{{ $payment->request->service->name ?? 'Service unavailable' }}</td>

                        <td>
                            {{ $payment->request->office->name ?? 'Office unavailable' }}
                            <br>
                            <small class="muted">
                                {{ $payment->request->office->municipality->name ?? '' }}
                            </small>
                        </td>

                        <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>

                        <td>{{ ucfirst($payment->payment_method) }}</td>

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
                            <a href="{{ route('citizen.payments.show', $payment->id) }}">View</a>

                            @if($payment->status === 'success')
                                |
                                <a href="{{ route('citizen.payments.receipt.download', $payment->id) }}">
                                    Receipt
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>