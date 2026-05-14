<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Municipality Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Municipality Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.municipalities.index') }}" class="button secondary">Back</a>
            </div>

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
            <section class="grid two">
                <div class="panel">
                    <h2>Details</h2>
                    <p>
                        <strong>Region:</strong> {{ $municipality->region }}</p>
                        <p>
                            <strong>Status:</strong> <span class="badge">{{ $municipality->status }}</span>
                        </p>
                        <p>
                            <strong>Address:</strong> {{ $municipality->address->address_line_1 ?? 'No address' }}</p>
                        </div>
                        <div class="panel">
                            <h2>Map</h2>
                            @if($municipality->address && $municipality->address->latitude && $municipality->address->longitude)
                                <iframe class="map" src="https://maps.google.com/maps?q={{ $municipality->address->latitude }},{{ $municipality->address->longitude }}&z=15&output=embed">
                                </iframe>
                            @else
                                <p>No coordinates available.</p>
                            @endif
                        </div>
                    </section>
                    <div class="panel">
                        <h2>Offices</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($municipality->offices as $office)
                                    <tr>
                                        <td>{{ $office->name }}</td>
                                        <td>{{ $office->contact_email }}</td>
                                        <td>{{ $office->contact_phone }}</td>
                                        <td>
                                            <span class="badge">{{ $office->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No offices yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </main>
            </body>
        </html>