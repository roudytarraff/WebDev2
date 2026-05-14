<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Slots</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Appointment Slots</h1>
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
                <div class="panel-head">
                    <h2>Slots</h2>
                    <a class="button" href="{{ route('office.appointment-slots.create') }}">Create Slot</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($slots as $slot)<tr>
                        <td>{{ $slot->service->name ?? '' }}</td>
                        <td>{{ $slot->slot_date }}</td>
                        <td>{{ $slot->start_time }} - {{ $slot->end_time }}</td>
                        <td>{{ $slot->appointments->count() }}/{{ $slot->capacity }}</td>
                        <td>
                            <span class="badge">{{ $slot->status }}</span>
                        </td>
                        <td class="actions">
                            <a href="{{ route('office.appointment-slots.edit', $slot->id) }}">Edit</a>
                            <form method="POST" action="{{ route('office.appointment-slots.destroy', $slot->id) }}">@csrf
                                @method('DELETE')<button class="danger">Delete</button>
                            </form>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="6">No slots yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>