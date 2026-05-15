<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Appointment</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .appointment-page {
            max-width: 1180px;
        }

        .request-actions {
            display: flex;
            gap: 12px;
            margin: 18px 0 22px;
            flex-wrap: wrap;
        }

        .appointment-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 22px;
            align-items: start;
        }

        .summary-list p {
            margin: 0 0 16px;
            line-height: 1.5;
        }

        .date-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .date-option {
            display: block;
            padding: 12px 14px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            text-decoration: none;
        }

        .date-option:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .date-option.active {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .slot-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .slot-card:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .slot-card input[type="radio"] {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            accent-color: #2563eb;
            flex: 0 0 auto;
        }

        .slot-title {
            display: block;
            font-weight: 800;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .slot-meta {
            display: block;
            color: #64748b;
            font-size: 14px;
            line-height: 1.4;
        }

        .appointment-submit-row {
            margin-top: 20px;
        }

        .appointment-submit-row button {
            width: auto;
            min-width: 220px;
        }

        .empty-state {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 10px;
            padding: 22px;
            color: #64748b;
        }

        @media (max-width: 1000px) {
            .appointment-grid {
                grid-template-columns: 1fr;
            }

            .slots-grid {
                grid-template-columns: 1fr;
            }

            .appointment-submit-row button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main appointment-page">
    <header class="topbar">
        <div>
            <h1>Select Appointment</h1>
            <p>Step 3 of 5</p>
        </div>
    </header>

    <div class="request-actions">
        <a href="{{ route('citizen.service-requests.documents') }}" class="button secondary">
            Back
        </a>

        <a href="{{ route('citizen.service-requests.cancel') }}" class="button secondary">
            Cancel
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

    <section class="appointment-grid">
        <div class="panel">
            <div class="panel-head">
                <h2>Available Dates</h2>
                <span class="badge">{{ $availableDates->count() }} Dates</span>
            </div>

            <div class="summary-list">
                <p><strong>Service:</strong> {{ $service->name }}</p>
                <p><strong>Office:</strong> {{ $service->office->name }}</p>
                <p><strong>Selected Date:</strong> {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
            </div>

            @if($availableDates->count())
                <div class="date-list">
                    @foreach($availableDates as $date)
                        <a
                            href="{{ route('citizen.service-requests.appointment', ['date' => $date]) }}"
                            class="date-option {{ $selectedDate == $date ? 'active' : '' }}"
                        >
                            {{ \Illuminate\Support\Carbon::parse($date)->format('D, M d, Y') }}
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    No available dates right now.
                </div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Available Time Slots</h2>
                <span class="badge">{{ $slots->count() }} Slots</span>
            </div>

            @if($slots->count())
                <form method="POST" action="{{ route('citizen.service-requests.appointment.store') }}">
                    @csrf

                    <div class="slots-grid">
                        @foreach($slots as $slot)
                            <label class="slot-card">
                                <input
                                    type="radio"
                                    name="slot_id"
                                    value="{{ $slot->id }}"
                                    {{ old('slot_id', $wizard['slot_id'] ?? null) == $slot->id ? 'checked' : '' }}
                                    required
                                >

                                <span>
                                    <span class="slot-title">
                                        {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                    </span>

                                    <span class="slot-meta">
                                        Date: {{ \Illuminate\Support\Carbon::parse($slot->slot_date)->format('M d, Y') }}
                                    </span>

                                    <span class="slot-meta">
                                        Capacity left: {{ $slot->capacity }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="appointment-submit-row">
                        <button type="submit">
                            Continue to Payment
                        </button>
                    </div>
                </form>
            @else
                <div class="empty-state">
                    No slots available for this date. Please select another date.
                </div>
            @endif
        </div>
    </section>
</main>
</body>
</html>