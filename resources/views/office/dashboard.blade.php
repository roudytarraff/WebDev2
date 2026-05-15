<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="stats-grid">
                <div class="stat"><span>Total Requests</span><strong>{{ $requestsCount }}</strong></div>
                <div class="stat"><span>Pending</span><strong>{{ $pendingRequests }}</strong></div>
                <div class="stat"><span>Completed</span><strong>{{ $completedRequests }}</strong></div>
                <div class="stat"><span>Appointments Today</span><strong>{{ $appointmentsToday }}</strong></div>
                <div class="stat"><span>Revenue</span><strong>${{ number_format($revenue, 2) }}</strong></div>
                <div class="stat"><span>Average Rating</span><strong>{{ number_format($averageRating ?? 0, 1) }}/5</strong></div>
            </section>

            {{-- Live feed toast container --}}
            <div id="liveFeed" style="position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:8px;z-index:9999;max-width:340px;"></div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Recent Requests</h2>
                    <a href="{{ route('office.requests.index') }}">View all</a>
                </div>
                <table id="recentRequestsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Citizen</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody id="recentRequestsBody">
                        @forelse($recentRequests as $req)
                            <tr>
                                <td><a href="{{ route('office.requests.show', $req->id) }}">{{ $req->request_number }}</a></td>
                                <td>{{ $req->citizen->full_name ?? '' }}</td>
                                <td>{{ $req->service->name ?? '' }}</td>
                                <td><span class="badge">{{ str_replace('_', ' ', $req->status) }}</span></td>
                                <td>{{ $req->assignedTo->full_name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr id="emptyRow"><td colspan="5">No requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>

        @vite(['resources/js/app.js'])

        <script>
            const officeId = {{ $office->id ?? 'null' }};
            const feed     = document.getElementById('liveFeed');

            function showToast(msg, color) {
                const t = document.createElement('div');
                t.style.cssText = `background:${color};color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.2);font-size:14px;line-height:1.4;`;
                t.textContent = msg;
                feed.appendChild(t);
                setTimeout(() => t.remove(), 6000);
            }

            function prependRequest(data) {
                const tbody = document.getElementById('recentRequestsBody');
                document.getElementById('emptyRow')?.remove();
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><a href="${data.url}">${data.request_number}</a></td><td>${data.citizen_name}</td><td>${data.service_name}</td><td><span class="badge">${data.status}</span></td><td>Unassigned</td>`;
                tbody.prepend(tr);
            }

            if (officeId && window.Echo) {
                window.Echo.private(`office.${officeId}`)
                    .listen('.request.new', (data) => {
                        showToast(`New request: ${data.request_number} — ${data.citizen_name}`, '#2563eb');
                        prependRequest(data);
                    })
                    .listen('.document.uploaded', (data) => {
                        showToast(`Document uploaded on ${data.request_number} by ${data.uploaded_by}`, '#7c3aed');
                    });
            }
        </script>
    </body>
</html>
