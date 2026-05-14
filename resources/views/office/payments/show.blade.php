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
            </div>

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
            <section class="grid two">
                <div class="panel">
                    <h2>Payment</h2>
                    <p>
                        <strong>Request:</strong> {{ $payment->request->request_number ?? '' }}</p>
                        <p>
                            <strong>Citizen:</strong> {{ $payment->request->citizen->full_name ?? '' }}</p>
                            <p>
                                <strong>Amount:</strong> {{ $payment->currency }} {{ number_format($payment->amount, 2) }}</p>
                                <p>
                                    <strong>Method:</strong> {{ $payment->payment_method }}</p>
                                    <p>
                                        <strong>Provider:</strong> {{ $payment->provider }}</p>
                                        <p>
                                            <strong>Reference:</strong> {{ $payment->transaction_reference }}</p>
                                            <p>
                                                <strong>Status:</strong> <span class="badge">{{ $payment->status }}</span>
                                            </p>
                                        </div>
                                        <div class="panel">
                                            <h2>Transactions</h2>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Reference</th>
                                                        <th>Hash</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>@forelse($payment->transactions as $transaction)<tr>
                                                    <td>{{ $transaction->transaction_type }}</td>
                                                    <td>{{ $transaction->provider_reference }}</td>
                                                    <td>{{ $transaction->tx_hash }}</td>
                                                    <td>{{ $transaction->status }}</td>
                                                </tr>@empty<tr>
                                                <td colspan="4">No transactions.</td>
                                            </tr>@endforelse</tbody>
                                        </table>
                                    </div>
                                </section>
                            </main>
                        </body>
                    </html>