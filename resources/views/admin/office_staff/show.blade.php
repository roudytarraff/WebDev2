<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Staff Assignment</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>{{ $staff->user->full_name }}</h1>
                    <p>{{ $staff->job_title }} at {{ $staff->office->name }}</p>
                </div>
                <a href="{{ route('admin.office-staff.edit', $staff->id) }}" class="button">Edit Staff</a>
            </header>

            <div class="back-row">
                <a href="{{ route('admin.office-staff.index') }}" class="button secondary">Back</a>
            </div>

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
                    <span>Total Assigned</span>
                    <strong>{{ $requestStats['total'] }}</strong>
                </div>
                <div class="stat">
                    <span>Active Load</span>
                    <strong>{{ $requestStats['active'] }}</strong>
                </div>
                <div class="stat">
                    <span>Completed</span>
                    <strong>{{ $requestStats['completed'] }}</strong>
                </div>
                <div class="stat">
                    <span>Rejected</span>
                    <strong>{{ $requestStats['rejected'] }}</strong>
                </div>
            </section>

            <section class="grid two">
                <div class="panel">
                    <h2>Staff Details</h2>
                    <p><strong>Name:</strong> {{ $staff->user->full_name }}</p>
                    <p><strong>Email:</strong> {{ $staff->user->email }}</p>
                    <p><strong>Phone:</strong> {{ $staff->user->phone ?? 'Not set' }}</p>
                    <p><strong>User Status:</strong> <span class="badge">{{ $staff->user->status }}</span></p>
                    <p><strong>Assignment Status:</strong> <span class="badge">{{ $staff->status }}</span></p>
                    <p><strong>Job Title:</strong> {{ $staff->job_title }}</p>
                    <p><strong>User Roles:</strong> {{ $staff->user->roles->pluck('name')->implode(', ') ?: 'No roles' }}</p>
                    <p><strong>Assigned Since:</strong> {{ $staff->created_at }}</p>
                </div>

                <div class="panel">
                    <h2>Office Details</h2>
                    <p><strong>Office:</strong> {{ $staff->office->name }}</p>
                    <p><strong>Municipality:</strong> {{ $staff->office->municipality->name ?? 'No municipality' }}</p>
                    <p><strong>Email:</strong> {{ $staff->office->contact_email ?? 'Not set' }}</p>
                    <p><strong>Phone:</strong> {{ $staff->office->contact_phone ?? 'Not set' }}</p>
                    <p><strong>Status:</strong> <span class="badge">{{ $staff->office->status }}</span></p>
                    <p><strong>Address:</strong> {{ $staff->office->address->address_line_1 ?? 'No address' }}</p>
                    <p><strong>Location:</strong> {{ $staff->office->address->latitude ?? 'No latitude' }}, {{ $staff->office->address->longitude ?? 'No longitude' }}</p>
                    <div class="actions">
                        <a href="{{ route('admin.offices.show', $staff->office->id) }}" class="button secondary">View Office</a>
                    </div>
                </div>
            </section>

            <div class="panel">
                <div class="panel-head">
                    <h2>Recent Assigned Requests</h2>
                    <span class="badge">{{ $recentRequests->count() }} shown</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ $request->citizen->full_name ?? '' }}</td>
                                <td>{{ $request->service->name ?? '' }}</td>
                                <td><span class="badge">{{ str_replace('_', ' ', $request->status) }}</span></td>
                                <td>{{ $request->submitted_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No requests assigned to this staff member yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($otherOfficeAssignments->count() > 0)
                <div class="panel">
                    <div class="panel-head">
                        <h2>Other Office Assignments</h2>
                        <span class="badge">{{ $otherOfficeAssignments->count() }}</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Municipality</th>
                                <th>Job Title</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otherOfficeAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->office->name }}</td>
                                    <td>{{ $assignment->office->municipality->name ?? '' }}</td>
                                    <td>{{ $assignment->job_title }}</td>
                                    <td><span class="badge">{{ $assignment->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </main>
    </body>
</html>
