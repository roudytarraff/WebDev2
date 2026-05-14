<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Slot</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Edit Slot</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.appointment-slots.index') }}" class="button secondary">Back</a>
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
            <form method="POST" action="{{ route('office.appointment-slots.update', $slot->id) }}">@csrf
                @if(isset($slot)) @method('PUT') @endif
                    <div>
                        <label>Service</label>
                        <select name="service_id">@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('service_id', $slot->service_id ?? '') == $service->id)>{{ $service->name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label>Date</label>
                        <input type="date" name="slot_date" value="{{ old('slot_date', $slot->slot_date ?? '') }}">
                    </div>
                    <div>
                        <label>Start Time</label>
                        <input type="time" name="start_time" value="{{ old('start_time', isset($slot) ? substr($slot->start_time, 0, 5) : '') }}">
                    </div>
                    <div>
                        <label>End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', isset($slot) ? substr($slot->end_time, 0, 5) : '') }}">
                    </div>
                    <div>
                        <label>Capacity</label>
                        <input type="number" name="capacity" value="{{ old('capacity', $slot->capacity ?? 1) }}">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">@foreach(['available','full','disabled'] as $status)<option value="{{ $status }}" @selected(old('status', $slot->status ?? 'available') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                    </div>
                    <div>
                        <button>Save Slot</button>
                    </div>
                </form>
            </main>
        </body>
    </html>