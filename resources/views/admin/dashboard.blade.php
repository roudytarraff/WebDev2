<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Admin Dashboard</h1>
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
                    <span>Users</span>
                    <strong>{{ $usersCount }}</strong>
                </div>
                <div class="stat">
                    <span>Municipalities</span>
                    <strong>{{ $municipalitiesCount }}</strong>
                </div>
                <div class="stat">
                    <span>Offices</span>
                    <strong>{{ $officesCount }}</strong>
                </div>
                <div class="stat">
                    <span>Total Requests</span>
                    <strong>{{ $requestsCount }}</strong>
                </div>
                <div class="stat">
                    <span>Pending Requests</span>
                    <strong>{{ $pendingRequests }}</strong>
                </div>
                <div class="stat">
                    <span>Revenue</span>
                    <strong>${{ number_format($revenue, 2) }}</strong>
                </div>
            </section>

            <section class="grid two">
                <div class="panel">
                    <div class="panel-head">
                        <h2>Requests Per Office</h2>
                        <a href="{{ route('admin.reports.index') }}">Reports</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Requests</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requestsByOffice as $office)
                                <tr>
                                    <td>{{ $office->name }}</td>
                                    <td>{{ $office->service_requests_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No offices yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h2>Recent Requests</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Citizen</th>
                                <th>Office</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRequests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>{{ $request->citizen->full_name ?? 'Citizen' }}</td>
                                    <td>{{ $request->office->name ?? 'Office' }}</td>
                                    <td>
                                        <span class="badge">{{ str_replace('_', ' ', $request->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Municipalities & Offices Map</h2>
                    <span class="muted">Click a marker to open details</span>
                </div>

                @if(config('services.google.maps_api_key'))
                    <div id="adminMap" class="map large">
                    </div>
                    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}">
                    </script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                        const points = @json($mapMunicipalities->merge($mapOffices)->values());
                        const map = new google.maps.Map(document.getElementById('adminMap'), {
                        center: { lat: 33.8938, lng: 35.5018 },
                        zoom: 12,
                        mapTypeControl: false,
                        streetViewControl: false
                        });
                        const bounds = new google.maps.LatLngBounds();
                        const info = new google.maps.InfoWindow();

                        points.forEach(function (point) {
                        const position = { lat: point.lat, lng: point.lng };
                        const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: point.name,
                        label: point.type === 'office' ? 'O' : 'M'
                        });

                        bounds.extend(position);

                        marker.addListener('click', function () {
                        info.setContent(`
                        <div style="min-width:220px">
                            <strong>${point.name}</strong>
                            <br>
                            <span>${point.type}</span>
                            <br>
                            ${point.municipality ? `<span>${point.municipality}</span>
                            <br>` : ''}
                            ${point.email ? `<span>${point.email}</span>
                            <br>` : ''}
                            ${point.phone ? `<span>${point.phone}</span>
                            <br>` : ''}
                            <span>Status: ${point.status}</span>
                            <br>
                            <a href="${point.url}">Open details</a>
                        </div>
                        `);
                        info.open(map, marker);
                        });
                        });

                        if (points.length > 0) {
                        map.fitBounds(bounds);
                        }
                        });
                    </script>
                @else
                    <p class="muted">Add <strong>GOOGLE_MAPS_API_KEY</strong> in your <strong>.env</strong> file to show all office and municipality markers here.</p>
                @endif
            </section>
        </main>
    </body>
</html>