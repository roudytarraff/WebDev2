<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Office Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Office Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.offices.index') }}" class="button secondary">Back</a>
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
                        <strong>Municipality:</strong> {{ $office->municipality->name ?? 'No municipality' }}</p>
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
                                    <h2>Google Map</h2>
                                    @if($office->address && $office->address->latitude && $office->address->longitude)
                                        <iframe class="map" src="https://maps.google.com/maps?q={{ $office->address->latitude }},{{ $office->address->longitude }}&z=15&output=embed">
                                        </iframe>
                                    @else
                                        <p>No coordinates available.</p>
                                    @endif
                                </div>
                            </section>
                            <section class="grid two">
                                <div class="panel">
                                    <h2>Staff</h2>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>@forelse($office->staff as $staff)<tr>
                                            <td>{{ $staff->user->full_name }}</td>
                                            <td>{{ $staff->job_title }}</td>
                                            <td>
                                                <span class="badge">{{ $staff->status }}</span>
                                            </td>
                                        </tr>@empty<tr>
                                        <td colspan="3">No staff assigned.</td>
                                    </tr>@endforelse</tbody>
                                </table>
                            </div>
                            <div class="panel">
                                <h2>Services</h2>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>@forelse($office->services as $service)<tr>
                                        <td>{{ $service->name }}</td>
                                        <td>{{ $service->price }}</td>
                                        <td>
                                            <span class="badge">{{ $service->status }}</span>
                                        </td>
                                    </tr>@empty<tr>
                                    <td colspan="3">No services yet.</td>
                                </tr>@endforelse</tbody>
                            </table>
                        </div>
                    </section>
                </main>
            </body>
        </html>