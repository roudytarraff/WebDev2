<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .payment-page {
            max-width: 1180px;
        }

        .request-actions {
            display: flex;
            gap: 12px;
            margin: 18px 0 22px;
            flex-wrap: wrap;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            align-items: start;
        }

        .payment-panel {
            min-height: 265px;
        }

        .summary-list p {
            margin: 0 0 16px;
            line-height: 1.5;
        }

        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 14px;
        }

        .payment-option {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .payment-option:hover {
            background: #f1f5f9;
            border-color: #2563eb;
        }

        .payment-option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 3px 0 0 0;
            flex: 0 0 auto;
            accent-color: #2563eb;
            cursor: pointer;
        }

        .payment-option-content {
            flex: 1;
        }

        .payment-option-title {
            display: block;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .payment-option-text {
            display: block;
            color: #64748b;
            font-size: 14px;
            line-height: 1.4;
        }

        .payment-submit-row {
            margin-top: 18px;
        }

        .payment-submit-row button {
            min-width: 220px;
        }

        @media (max-width: 900px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }

            .payment-panel {
                min-height: auto;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main payment-page">
    <header class="topbar">
        <div>
            <h1>Payment</h1>
            <p>Step 4 of 5</p>
        </div>
    </header>

    <div class="request-actions">
        @if($service->requires_appointment)
            <a href="{{ route('citizen.service-requests.appointment') }}" class="button secondary">
                Back
            </a>
        @else
            <a href="{{ route('citizen.service-requests.documents') }}" class="button secondary">
                Back
            </a>
        @endif

        <a href="{{ route('citizen.service-requests.cancel') }}" class="button secondary">
            Cancel
        </a>
    </div>

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="payment-grid">
        <div class="panel payment-panel">
            <div class="panel-head">
                <h2>Payment Summary</h2>
                <span class="badge">Amount</span>
            </div>

            <div class="summary-list">
                <p><strong>Service:</strong> {{ $service->name }}</p>
                <p><strong>Office:</strong> {{ $service->office->name }}</p>
                <p><strong>Amount:</strong> ${{ number_format((float) $service->price, 2) }}</p>
            </div>
        </div>

        <div class="panel payment-panel">
            <div class="panel-head">
                <h2>Choose Payment Method</h2>
                <span class="badge">Required</span>
            </div>

            <form method="POST" action="{{ route('citizen.service-requests.payment.store') }}">
                @csrf

                <div class="payment-options">
                    @if($service->supports_online_payment)
                        <label class="payment-option">
                            <input
                                type="radio"
                                name="payment_method"
                                value="card"
                                {{ old('payment_method', $wizard['payment_method'] ?? null) === 'card' ? 'checked' : '' }}
                                required
                            >

                            <span class="payment-option-content">
                                <span class="payment-option-title">Card Payment</span>
                                <span class="payment-option-text">Pay online using a bank card.</span>
                            </span>
                        </label>
                    @endif

                    @if($service->supports_crypto_payment)
                        <label class="payment-option">
                            <input
                                type="radio"
                                name="payment_method"
                                value="crypto"
                                {{ old('payment_method', $wizard['payment_method'] ?? null) === 'crypto' ? 'checked' : '' }}
                                required
                            >

                            <span class="payment-option-content">
                                <span class="payment-option-title">Crypto Payment</span>
                                <span class="payment-option-text">Pay using cryptocurrency if supported by this service.</span>
                            </span>
                        </label>
                    @endif

                    <label class="payment-option">
                        <input
                            type="radio"
                            name="payment_method"
                            value="cash"
                            {{ old('payment_method', $wizard['payment_method'] ?? null) === 'cash' ? 'checked' : '' }}
                            required
                        >

                        <span class="payment-option-content">
                            <span class="payment-option-title">Pay at Office</span>
                            <span class="payment-option-text">Submit now and pay later at the office.</span>
                        </span>
                    </label>
                </div>

                <div class="payment-submit-row">
                    <button type="submit">
                        Continue to Review
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>