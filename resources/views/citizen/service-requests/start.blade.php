<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Service Request</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .request-page {
            max-width: 1180px;
        }

        .request-actions {
            display: flex;
            gap: 12px;
            margin: 18px 0 22px;
            flex-wrap: wrap;
        }

        .request-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            align-items: start;
        }

        .request-panel {
            min-height: 295px;
        }

        .request-panel h2 {
            margin-bottom: 18px;
        }

        .summary-list p {
            margin: 0 0 16px;
            line-height: 1.5;
        }

        .request-form-group {
            margin-top: 12px;
        }

        .request-form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .request-form-group textarea {
            width: 100%;
            min-height: 170px;
            resize: vertical;
            display: block;
            box-sizing: border-box;
        }

        .request-submit-row {
            margin-top: 16px;
            display: flex;
            justify-content: flex-start;
        }

        .request-submit-row button {
            min-width: 220px;
        }

        @media (max-width: 900px) {
            .request-grid {
                grid-template-columns: 1fr;
            }

            .request-panel {
                min-height: auto;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main request-page">
    <header class="topbar">
        <div>
            <h1>Start Service Request</h1>
            <p>{{ $service->name }}</p>
        </div>
    </header>

    <div class="request-actions">
        <a href="{{ route('discovery.services.show', $service->id) }}" class="button secondary">
            Back to Service
        </a>

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

    <section class="request-grid">
        <div class="panel request-panel">
            <div class="panel-head">
                <h2>Service Summary</h2>
                <span class="badge">Step 1</span>
            </div>

            <div class="summary-list">
                <p><strong>Service:</strong> {{ $service->name }}</p>
                <p><strong>Office:</strong> {{ $service->office->name }}</p>
                <p><strong>Category:</strong> {{ $service->category->name ?? 'No category' }}</p>
                <p><strong>Price:</strong> ${{ number_format((float) $service->price, 2) }}</p>
                <p><strong>Appointment Required:</strong> {{ $service->requires_appointment ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        <div class="panel request-panel">
            <div class="panel-head">
                <h2>Step 1 of 5: Request Details</h2>
                <span class="badge">Details</span>
            </div>

            <form method="POST" action="{{ route('citizen.service-requests.details.store', $service->id) }}">
                @csrf

                <div class="request-form-group">
                    <label for="description">Request Description</label>

                    <textarea
                        id="description"
                        name="description"
                        required
                        placeholder="Explain why you need this service...">{{ old('description') }}</textarea>
                </div>

                <div class="request-submit-row">
                    <button type="submit">
                        Continue to Documents
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>