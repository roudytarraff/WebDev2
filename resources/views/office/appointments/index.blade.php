<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointments</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Appointments</h1>
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
            <div class="panel">
                <table>
                    <thead>
                        <tr>
                            <th>Citizen</th>
                            <th>Request</th>
                            <th>Service</th>
                            <th>Slot</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($appointments as $appointment)<tr>
                        <td>{{ $appointment->citizen->full_name ?? '' }}</td>
                        <td>{{ $appointment->request->request_number ?? '' }}</td>
                        <td>{{ $appointment->request->service->name ?? '' }}</td>
                        <td>{{ $appointment->slot->slot_date ?? '' }} {{ $appointment->slot->start_time ?? '' }}</td>
                        <td>
                            <span class="badge">{{ $appointment->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('office.appointments.show', $appointment->id) }}">Manage</a>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="6">No appointments yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>