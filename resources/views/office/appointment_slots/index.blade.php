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
                <p>{{ Auth::user()->full_name ?? '' }} - {{ $office->name }}</p>
            </div>
        </header>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section class="stats-grid">
            <div class="stat">
                <span>Total Slots</span>
                <strong>{{ $totalSlots }}</strong>
            </div>

            <div class="stat">
                <span>Available</span>
                <strong>{{ $availableSlots }}</strong>
            </div>

            <div class="stat">
                <span>Full</span>
                <strong>{{ $fullSlots }}</strong>
            </div>

            <div class="stat">
                <span>Disabled</span>
                <strong>{{ $disabledSlots }}</strong>
            </div>
        </section>

        <div class="panel">
            <div class="panel-head">
                <h2>Filter Slots</h2>
                <a class="button" href="{{ route('office.appointment-slots.create') }}">Create Slot</a>
            </div>

            <form method="GET" action="{{ route('office.appointment-slots.index') }}">
                <div>
                    <label>Service</label>
                    <select name="service_id">
                        <option value="">All Services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected((string) $selectedServiceId === (string) $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        @foreach(['available', 'full', 'disabled'] as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Date</label>
                    <input type="date" name="slot_date" value="{{ $selectedDate }}">
                </div>

                <div>
                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('office.appointment-slots.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Slots List</h2>
                <span class="badge">{{ $slots->count() }} slots</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Booked</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($slots as $slot)
                        @php
                            $booked = $slot->booked_appointments_count ?? 0;
                            $remaining = max(0, $slot->capacity - $booked);
                        @endphp

                        <tr>
                            <td>{{ $slot->service->name ?? 'Service removed' }}</td>
                            <td>{{ $slot->slot_date }}</td>
                            <td>{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</td>
                            <td>{{ $slot->capacity }}</td>
                            <td>{{ $booked }}</td>
                            <td>{{ $remaining }}</td>
                            <td>
                                <span class="badge">{{ $slot->status }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('office.appointment-slots.edit', $slot->id) }}">Edit</a>

                                <form method="POST" action="{{ route('office.appointment-slots.destroy', $slot->id) }}" onsubmit="return confirm('Are you sure you want to delete this slot?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No appointment slots found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>