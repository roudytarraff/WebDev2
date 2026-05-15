<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Appointment Slot</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
    @include('office.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Create Appointment Slot</h1>
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

        @if($services->isEmpty())
            <div class="alert error">
                No active services requiring appointments were found.
                Go to Office > Services, create or edit a service, set Status to Active, and set Requires Appointment to Yes.
            </div>
        @endif

        <form method="POST" action="{{ route('office.appointment-slots.store') }}">
            @csrf

            <div>
                <label>Service</label>
                <select name="service_id" required>
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                            {{ $service->name }} - {{ $service->duration_minutes }} minutes
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Date</label>
                <input type="date" name="slot_date" value="{{ old('slot_date') }}" min="{{ now()->toDateString() }}" required>
            </div>

            <div>
                <label>Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time') }}" required>
            </div>

            <div>
                <label>End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time') }}" required>
            </div>

            <div>
                <label>Capacity</label>
                <input type="number" name="capacity" min="1" value="{{ old('capacity', 1) }}" required>
            </div>

            <div>
                <label>Status</label>
                <select name="status" required>
                    @foreach(['available', 'full', 'disabled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'available') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button @disabled($services->isEmpty())>Create Slot</button>
            </div>
        </form>

        <div class="panel">
            <h2>Important Rules</h2>
            <p class="muted">
                The slot date cannot be in the past. The slot time must be inside the office working hours.
                The same service cannot have overlapping active slots at the same time.
            </p>
        </div>
    </main>
</body>
</html>