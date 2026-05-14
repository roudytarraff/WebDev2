<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Municipalities</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Municipalities</h1>
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
                    <h2>Municipalities Map</h2>
                    <span class="muted">{{ $mapMunicipalities->count() }} locations</span>
                </div>

                @if(config('services.google.maps_api_key') && $mapMunicipalities->count() > 0)
                    <div id="municipalitiesMap" class="map large"></div>

                    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const points = @json($mapMunicipalities);
                            const map = new google.maps.Map(document.getElementById('municipalitiesMap'), {
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
                                    label: 'M'
                                });

                                bounds.extend(position);

                                marker.addListener('click', function () {
                                    info.setContent(`
                                        <div style="min-width:220px">
                                            <strong>${point.name}</strong>
                                            <br>
                                            <span>${point.region ?? ''}</span>
                                            <br>
                                            <span>${point.address ?? ''}</span>
                                            <br>
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
                    <p class="muted">Add <strong>GOOGLE_MAPS_API_KEY</strong> in your <strong>.env</strong> file to show municipality markers here.</p>
                @else
                    <p class="muted">No municipality coordinates found yet.</p>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Municipalities</h2>
                    <a class="button" href="{{ route('admin.municipalities.create') }}">Create Municipality</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Region</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($municipalities as $municipality)
                            <tr>
                                <td>{{ $municipality->name }}</td>
                                <td>{{ $municipality->region }}</td>
                                <td>{{ $municipality->address->address_line_1 ?? 'No address' }}</td>
                                <td>
                                    <span class="badge">{{ $municipality->status }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.municipalities.show', $municipality->id) }}">View</a>
                                    <a href="{{ route('admin.municipalities.edit', $municipality->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.municipalities.toggle-status', $municipality->id) }}">@csrf<button type="submit">Toggle</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.municipalities.destroy', $municipality->id) }}">@csrf
                                        @method('DELETE')<button class="danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No municipalities found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>
