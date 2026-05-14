<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Offices</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Offices</h1>
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
                    <h2>Offices Map</h2>
                    <span class="muted">{{ $mapOffices->count() }} locations</span>
                </div>

                @if(config('services.google.maps_api_key') && $mapOffices->count() > 0)
                    <div id="officesMap" class="map large"></div>

                    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const points = @json($mapOffices);
                            const map = new google.maps.Map(document.getElementById('officesMap'), {
                                center: { lat: 33.8938, lng: 35.5018 },
                                zoom: 10,
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
                                    label: 'O'
                                });

                                bounds.extend(position);

                                marker.addListener('click', function () {
                                    info.setContent(`
                                        <div style="min-width:240px">
                                            <strong>${point.name}</strong>
                                            <br>
                                            <span>${point.municipality ?? ''}</span>
                                            <br>
                                            <span>${point.address ?? ''}</span>
                                            <br>
                                            ${point.email ? `<span>${point.email}</span><br>` : ''}
                                            ${point.phone ? `<span>${point.phone}</span><br>` : ''}
                                            <span>Status: ${point.status}</span>
                                            <br>
                                            <a href="${point.url}">Open details</a>
                                        </div>
                                    `);
                                    info.open(map, marker);
                                });
                            });

                            map.fitBounds(bounds);
                        });
                    </script>
                @elseif(! config('services.google.maps_api_key'))
                    <p class="muted">Add <strong>GOOGLE_MAPS_API_KEY</strong> in your <strong>.env</strong> file to show office markers here.</p>
                @else
                    <p class="muted">No office coordinates found yet.</p>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Offices</h2>
                    <a class="button" href="{{ route('admin.offices.create') }}">Create Office</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Municipality</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offices as $office)
                            <tr>
                                <td>{{ $office->name }}</td>
                                <td>{{ $office->municipality->name ?? 'No municipality' }}</td>
                                <td>{{ $office->contact_email }}<br>{{ $office->contact_phone }}</td>
                                <td>
                                    <span class="badge">{{ $office->status }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.offices.show', $office->id) }}">View</a>
                                    <a href="{{ route('admin.offices.edit', $office->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.offices.toggle-status', $office->id) }}">@csrf<button>Toggle</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.offices.destroy', $office->id) }}">@csrf
                                        @method('DELETE')<button class="danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No offices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>
