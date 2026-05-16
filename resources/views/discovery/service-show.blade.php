<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $service->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .public-main {
            margin-left: 0;
            max-width: 1200px;
            margin-right: auto;
            margin-left: auto;
            padding: 24px;
        }

        .public-nav {
            background: #111827;
            padding: 16px 24px;
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .public-nav a {
            color: #dbeafe;
            font-weight: 700;
        }

        .public-nav .brand {
            color: #ffffff;
            font-size: 20px;
        }
    </style>
</head>

<body>
    @auth
        @if(auth()->user()?->isAdmin())
            @include('admin.navbar')
        @elseif(auth()->user()?->isOfficeStaff())
            @include('office.navbar')
        @else
            @include('citizen.navbar')
        @endif
    @else
        <nav class="public-nav">
            <a class="brand" href="{{ route('discovery.index') }}">E-Services Portal</a>

            <div>
                <a href="{{ route('discovery.index') }}">Discover Services</a>
                <a href="{{ route('auth.login') }}">Login</a>
                <a href="{{ route('auth.register') }}">Register</a>
            </div>
        </nav>
    @endauth

    <main class="{{ auth()->check() ? 'main' : 'public-main' }}">
        <div class="back-row">
            <a href="{{ route('discovery.offices.show', $service->office->id) }}" class="button secondary">
                Back to Office
            </a>

            <a href="{{ route('discovery.index') }}" class="button secondary">
                Back to Discovery
            </a>
        </div>

        <header class="topbar">
            <div>
                <h1>{{ $service->name }}</h1>
                <p>{{ $service->office->name }} - {{ $service->category->name ?? 'No category' }}</p>
            </div>
        </header>

        <section class="grid two">
            <div class="panel">
                <h2>Service Information</h2>

                <p><strong>Status:</strong> <span class="badge">{{ $service->status }}</span></p>
                <p><strong>Category:</strong> {{ $service->category->name ?? 'No category' }}</p>
                <p><strong>Price:</strong> ${{ number_format((float) $service->price, 2) }}</p>
                <p><strong>Duration:</strong> {{ $service->duration_minutes }} minutes</p>
                <p><strong>Appointment Required:</strong> {{ $service->requires_appointment ? 'Yes' : 'No' }}</p>
                <p><strong>Card Payment:</strong> {{ $service->supports_online_payment ? 'Supported' : 'Not Supported' }}</p>
                <p><strong>Crypto Payment:</strong> {{ $service->supports_crypto_payment ? 'Supported' : 'Not Supported' }}</p>
            </div>

            <div class="panel">
                <h2>Office Information</h2>

                <p><strong>Office:</strong> {{ $service->office->name }}</p>
                <p><strong>Municipality:</strong> {{ $service->office->municipality->name ?? '' }}</p>
                <p><strong>Email:</strong> {{ $service->office->contact_email ?? 'Not available' }}</p>
                <p><strong>Phone:</strong> {{ $service->office->contact_phone ?? 'Not available' }}</p>

                @if($service->office->address)
                    <p>
                        <strong>Address:</strong>
                        {{ $service->office->address->address_line_1 }},
                        {{ $service->office->address->city }},
                        {{ $service->office->address->region }}
                    </p>
                @endif
            </div>
        </section>

        <div class="panel">
            <h2>Description</h2>
            <p>{{ $service->description ?? 'No description available.' }}</p>
        </div>

        <div class="panel">
            <h2>Instructions</h2>
            <p>{{ $service->instructions ?? 'No instructions available.' }}</p>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Required Documents</h2>
                <span class="badge">{{ $service->documents->count() }} documents</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Required</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($service->documents as $document)
                        <tr>
                            <td>{{ $document->document_name }}</td>
                            <td>{{ $document->is_required ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No required documents defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Submit This Service Request</h2>
                <span class="badge">Citizen Service</span>
            </div>

            @auth
                @if(auth()->user()?->isAdmin() || auth()->user()?->isOfficeStaff())
                    <p class="muted">
                        Only citizens can submit service requests from this page.
                    </p>
                @else
                    @if($service->status === 'active')
                        <p>
                            Start a request for this service, upload the required documents,
                            complete payment if needed, and track your request later from My Requests.
                        </p>

                        <a class="button" href="{{ route('citizen.service-requests.start', $service->id) }}">
                            Start Request
                        </a>
                    @else
                        <p class="muted">
                            This service is currently not active, so requests cannot be submitted right now.
                        </p>
                    @endif
                @endif
            @else
                <p>Login or register to submit a request for this service.</p>

                <a class="button" href="{{ route('auth.login') }}">Login</a>
                <a class="button secondary" href="{{ route('auth.register') }}">Register</a>
            @endauth
        </div>
    </main>
</body>
</html>