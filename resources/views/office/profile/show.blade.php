<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Office Profile</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Office Profile</h1>
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
            <section class="grid two">
                <div class="panel">
                    <div class="panel-head">
                        <h2>Office Details</h2>
                        <a class="button" href="{{ route('office.profile.edit') }}">Edit Profile</a>
                    </div>
                    <p>
                        <strong>Municipality:</strong> {{ $office->municipality->name ?? '' }}</p>
                        <p>
                            <strong>Email:</strong> {{ $office->contact_email }}</p>
                            <p>
                                <strong>Phone:</strong> {{ $office->contact_phone }}</p>
                                <p>
                                    <strong>Status:</strong> <span class="badge">{{ $office->status }}</span>
                                </p>
                                <p>
                                    <strong>Address:</strong> {{ $office->address->address_line_1 ?? 'No address' }}</p>
                                </div>
                                <div class="panel">
                                    <h2>Google Maps Location</h2>
                                    @if($office->address && $office->address->latitude && $office->address->longitude)
                                        <iframe class="map" src="https://maps.google.com/maps?q={{ $office->address->latitude }},{{ $office->address->longitude }}&z=15&output=embed">
                                        </iframe>
                                    @else
                                        <p>Add latitude and longitude to show this office on Google Maps.</p>
                                    @endif
                                </div>
                            </section>
                        </main>
                    </body>
                </html>