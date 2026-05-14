<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Requests</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Service Requests</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
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

            <div class="panel">
                <div class="panel-head">
                    <h2>{{ $office->name }} Requests</h2>
                    <div class="actions">
                        @foreach(['pending','in_progress','approved','rejected','completed'] as $item)
                            <a href="{{ route('office.requests.index', ['status' => $item]) }}">{{ str_replace('_', ' ', $item) }}</a>
                        @endforeach
                        <a href="{{ route('office.requests.index') }}">All</a>
                    </div>
                </div>

                @if($status)
                    <p class="muted">Filtered by {{ str_replace('_', ' ', $status) }}</p>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Assigned To Me</h2>
                    <span class="badge">{{ $assignedRequests->count() }} requests</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedRequests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ $request->citizen->full_name ?? '' }}</td>
                                <td>{{ $request->service->name ?? '' }}</td>
                                <td><span class="badge">{{ str_replace('_', ' ', $request->status) }}</span></td>
                                <td>{{ $request->submitted_at }}</td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('office.requests.show', $request->id) }}">Manage</a>
                                        <a href="{{ route('office.requests.chat', $request->id) }}" class="button secondary">Chat</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No requests assigned to you.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Other Office Requests</h2>
                    <span class="badge">{{ $otherRequests->count() }} requests</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($otherRequests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ $request->citizen->full_name ?? '' }}</td>
                                <td>{{ $request->service->name ?? '' }}</td>
                                <td><span class="badge">{{ str_replace('_', ' ', $request->status) }}</span></td>
                                <td>{{ $request->assignedTo->full_name ?? 'Unassigned' }}</td>
                                <td>{{ $request->submitted_at }}</td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('office.requests.show', $request->id) }}">Manage</a>
                                        <a href="{{ route('office.requests.chat', $request->id) }}" class="button secondary">Chat</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No other requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>
