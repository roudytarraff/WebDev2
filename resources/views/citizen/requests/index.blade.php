<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
    @include('citizen.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>My Requests</h1>
                <p>Track your submitted service requests and their progress.</p>
            </div>

            <a href="{{ route('discovery.index') }}" class="button">Discover Services</a>
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
                <span>Total Requests</span>
                <strong>{{ $totalRequests }}</strong>
            </div>

            <div class="stat">
                <span>Pending</span>
                <strong>{{ $pendingRequests }}</strong>
            </div>

            <div class="stat">
                <span>In Progress</span>
                <strong>{{ $inProgressRequests }}</strong>
            </div>

            <div class="stat">
                <span>Completed</span>
                <strong>{{ $completedRequests }}</strong>
            </div>
        </section>

        <div class="panel">
            <div class="panel-head">
                <h2>Filter Requests</h2>
                <span class="badge">{{ $requests->count() }} matching</span>
            </div>

            <form method="GET" action="{{ route('citizen.requests.index') }}">
                <div>
                    <label>Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by request number, service, office..."
                    >
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($statusFilter === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('citizen.requests.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Request Tracking Dashboard</h2>
                <span class="badge">{{ $requests->count() }} requests</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Request Number</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Updates</th>
                        <th>Documents</th>
                        <th>Payments</th>
                        <th>Appointments</th>
                        <th>Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $serviceRequest)
                        <tr>
                            <td>
                                <strong>{{ $serviceRequest->request_number }}</strong>
                            </td>

                            <td>{{ $serviceRequest->service->name ?? 'Service unavailable' }}</td>

                            <td>
                                {{ $serviceRequest->office->name ?? 'Office unavailable' }}
                                <br>
                                <small class="muted">
                                    {{ $serviceRequest->office->municipality->name ?? '' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge">
                                    {{ ucwords(str_replace('_', ' ', $serviceRequest->status)) }}
                                </span>
                            </td>

                            <td>
                                @if($serviceRequest->submitted_at)
                                    {{ \Illuminate\Support\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
                                @else
                                    Not submitted
                                @endif
                            </td>

                            <td>{{ $serviceRequest->status_updates_count }}</td>
                            <td>{{ $serviceRequest->documents_count }}</td>
                            <td>{{ $serviceRequest->payments_count }}</td>
                            <td>{{ $serviceRequest->appointments_count }}</td>

                            <td>
                                <a href="{{ route('citizen.requests.show', $serviceRequest->id) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                No requests found.
                                Later, when the Service Request Submission task is completed, submitted requests will appear here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>