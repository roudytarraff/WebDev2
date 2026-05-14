<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Appointment Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.appointments.index') }}" class="button secondary">Back</a>
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
                    <h2>Appointment</h2>
                    <p>
                        <strong>Citizen:</strong> {{ $appointment->citizen->full_name ?? '' }}</p>
                        <p>
                            <strong>Request:</strong> {{ $appointment->request->request_number ?? '' }}</p>
                            <p>
                                <strong>Slot:</strong> {{ $appointment->slot->slot_date ?? '' }} {{ $appointment->slot->start_time ?? '' }} - {{ $appointment->slot->end_time ?? '' }}</p>
                                <p>
                                    <strong>Status:</strong> <span class="badge">{{ $appointment->status }}</span>
                                </p>
                                <p>{{ $appointment->notes }}</p>
                            </div>
                            <div class="panel">
                                <h2>Update</h2>
                                <form method="POST" action="{{ route('office.appointments.update', $appointment->id) }}">@csrf
                                    @method('PUT')<div>
                                    <label>Status</label>
                                    <select name="status">@foreach(['scheduled','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($appointment->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                                </div>
                                <div>
                                    <label>Notes</label>
                                    <textarea name="notes">{{ old('notes', $appointment->notes) }}</textarea>
                                </div>
                                <div>
                                    <button>Save Appointment</button>
                                </div>
                            </form>
                        </section>
                    </main>
                </body>
            </html>