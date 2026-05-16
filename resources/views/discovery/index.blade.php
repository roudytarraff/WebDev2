<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office & Service Discovery</title>
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

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 18px;
        }

        .office-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .service-list {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .service-mini {
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f8fafc;
        }

        .filter-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
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
        <header class="topbar">
            <div>
                <h1>Office & Service Discovery</h1>
                <p>Browse municipalities, offices, and available citizen services.</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat">
                <span>Matching Offices</span>
                <strong>{{ $offices->count() }}</strong>
            </div>

            <div class="stat">
                <span>Total Active Services</span>
                <strong>{{ $totalServices }}</strong>
            </div>

            <div class="stat">
                <span>Municipalities</span>
                <strong>{{ $municipalities->count() }}</strong>
            </div>
        </div>

        <div class="panel">
            <form method="GET" action="{{ route('discovery.index') }}" class="filter-form">
                <div class="field">
                    <label for="search">Search</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by office, service, city, region..."
                    >
                </div>

                <div class="field">
                    <label for="municipality_id">Municipality</label>
                    <select id="municipality_id" name="municipality_id">
                        <option value="">All Municipalities</option>

                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality->id }}" @selected((string) $municipalityId === (string) $municipality->id)>
                                {{ $municipality->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>

                        @foreach($categoryNames as $name)
                            <option value="{{ $name }}" @selected($categoryName === $name)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('discovery.index') }}">Reset</a>
                </div>
            </form>
        </div>

        @if($categoryName)
            <div class="filter-note">
                Showing offices that provide services in category: {{ $categoryName }}
            </div>
        @endif

        @forelse($offices as $office)
            <div class="office-card">
                <div class="panel-head">
                    <div>
                        <h2>{{ $office->name }}</h2>
                        <p class="muted">
                            {{ $office->municipality->name ?? 'No Municipality' }}

                            @if($office->address)
                                - {{ $office->address->city }}, {{ $office->address->region }}
                            @endif
                        </p>
                    </div>

                    <a class="button" href="{{ route('discovery.offices.show', $office->id) }}">View Office</a>
                </div>

                <p>
                    <strong>Email:</strong> {{ $office->contact_email ?? 'Not available' }}
                    <br>

                    <strong>Phone:</strong> {{ $office->contact_phone ?? 'Not available' }}
                    <br>

                    @if($categoryName)
                        <strong>Matching {{ $categoryName }} Services:</strong> {{ $office->matching_category_services_count }}
                        <br>
                        <strong>Total Active Services:</strong> {{ $office->active_services_count }}
                    @else
                        <strong>Active Services:</strong> {{ $office->active_services_count }}
                    @endif
                </p>

                <div class="service-list">
                    @forelse($office->services->take(3) as $service)
                        <div class="service-mini">
                            <strong>{{ $service->name }}</strong>
                            <br>

                            <span class="muted">
                                Category: {{ $service->category->name ?? 'No category' }}
                                -
                                ${{ number_format((float) $service->price, 2) }}
                                -
                                {{ $service->duration_minutes }} minutes
                            </span>

                            <br>

                            @if($service->requires_appointment)
                                <span class="badge">Appointment Required</span>
                            @else
                                <span class="badge">No Appointment Required</span>
                            @endif

                            @if($service->supports_online_payment)
                                <span class="badge">Card Payment</span>
                            @endif

                            @if($service->supports_crypto_payment)
                                <span class="badge">Crypto Payment</span>
                            @endif

                            <br>

                            <a href="{{ route('discovery.services.show', $service->id) }}">View Service Details</a>
                        </div>
                    @empty
                        @if($categoryName)
                            <p class="muted">No active services found in this selected category.</p>
                        @else
                            <p class="muted">No active services available for this office.</p>
                        @endif
                    @endforelse
                </div>

                @if($office->services->count() > 3)
                    <p class="muted">
                        Showing 3 services. Click View Office to see all services.
                    </p>
                @endif
            </div>
        @empty
            <div class="panel">
                <h2>No offices found</h2>
                <p>Try changing the search or filter values.</p>
            </div>
        @endforelse
    </main>
</body>
</html>