<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Office Dashboard</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Office Dashboard</h1>
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
            <section class="stats-grid">
                <div class="stat">
                    <span>Total Requests</span>
                    <strong>{{ $requestsCount }}</strong>
                </div>
                <div class="stat">
                    <span>Pending</span>
                    <strong>{{ $pendingRequests }}</strong>
                </div>
                <div class="stat">
                    <span>Completed</span>
                    <strong>{{ $completedRequests }}</strong>
                </div>
                <div class="stat">
                    <span>Appointments Today</span>
                    <strong>{{ $appointmentsToday }}</strong>
                </div>
                <div class="stat">
                    <span>Revenue</span>
                    <strong>${{ number_format($revenue, 2) }}</strong>
                </div>
                <div class="stat">
                    <span>Average Rating</span>
                    <strong>{{ number_format($averageRating ?? 0, 1) }}/5</strong>
                </div>
            </section>
            <div class="panel">
                <div class="panel-head">
                    <h2>Recent Requests</h2>
                    <a href="{{ route('office.requests.index') }}">View all</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('office.requests.show', $request->id) }}">{{ $request->request_number }}</a>
                                </td>
                                <td>{{ $request->citizen->full_name ?? '' }}</td>
                                <td>{{ $request->service->name ?? '' }}</td>
                                <td>
                                    <span class="badge">{{ str_replace('_', ' ', $request->status) }}</span>
                                </td>
                                <td>{{ $request->assignedTo->full_name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>