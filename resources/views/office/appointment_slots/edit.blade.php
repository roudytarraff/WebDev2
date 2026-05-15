<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment Slot</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
    @include('office.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Edit Appointment Slot</h1>
                <p>{{ Auth::user()->full_name ?? '' }} - {{ $office->name }}</p>
            </div>
        </header>

        <div class="back-row">
            <a href="{{ route('office.appointment-slots.index') }}" class="button secondary">Back to Slots</a>
        </div>

        @if($errors->any())
            <div class="alert error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @php
            $booked = $slot->booked_appointments_count ?? 0;
            $remaining = max(0, $slot->capacity - $booked);
        @endphp

        <section class="stats-grid">
            <div class="stat">
                <span>Current Capacity</span>
                <strong>{{ $slot->capacity }}</strong>
            </div>

            <div class="stat">
                <span>Booked</span>
                <strong>{{ $booked }}</strong>
            </div>

            <div class="stat">
                <span>Remaining</span>
                <strong>{{ $remaining }}</strong>
            </div>
        </section>

        <form method="POST" action="{{ route('office.appointment-slots.update', $slot->id) }}">
            @csrf
            @method('PUT')

            <div>
                <label>Service</label>
                <select name="service_id" required>
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $slot->service_id) == $service->id)>
                            {{ $service->name }} - {{ $service->duration_minutes }} minutes
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Date</label>
                <input type="date" name="slot_date" value="{{ old('slot_date', $slot->slot_date) }}" min="{{ now()->toDateString() }}" required>
            </div>

            <div>
                <label>Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time', substr($slot->start_time, 0, 5)) }}" required>
            </div>

            <div>
                <label>End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time', substr($slot->end_time, 0, 5)) }}" required>
            </div>

            <div>
                <label>Capacity</label>
                <input type="number" name="capacity" min="{{ $booked > 0 ? $booked : 1 }}" value="{{ old('capacity', $slot->capacity) }}" required>
            </div>

            <div>
                <label>Status</label>
                <select name="status" required>
                    @foreach(['available', 'full', 'disabled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $slot->status) === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button>Update Slot</button>
            </div>
        </form>

        <div class="panel">
            <h2>Editing Rules</h2>
            <p class="muted">
                You cannot reduce the capacity below the number of booked appointments.
                To stop citizens from booking this slot later, set the status to Disabled.
            </p>
        </div>
    </main>
</body>
</html>