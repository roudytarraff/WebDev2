<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $office->name }}</title>
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
            <a href="{{ route('discovery.index') }}" class="button secondary">Back to Discovery</a>
        </div>

        <header class="topbar">
            <div>
                <h1>{{ $office->name }}</h1>
                <p>{{ $office->municipality->name ?? '' }}</p>
            </div>
        </header>

        <section class="grid two">
            <div class="panel">
                <h2>Office Information</h2>

                <p><strong>Status:</strong> <span class="badge">{{ $office->status }}</span></p>
                <p><strong>Email:</strong> {{ $office->contact_email ?? 'Not available' }}</p>
                <p><strong>Phone:</strong> {{ $office->contact_phone ?? 'Not available' }}</p>

                @if($office->address)
                    <p>
                        <strong>Address:</strong>
                        {{ $office->address->address_line_1 }},
                        {{ $office->address->city }},
                        {{ $office->address->region }},
                        {{ $office->address->country }}
                    </p>
                @endif
            </div>

            <div class="panel">
                <h2>Working Hours</h2>

                @php
                    $days = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        7 => 'Sunday',
                    ];
                @endphp

                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($office->workingHours as $hour)
                            <tr>
                                <td>{{ $days[$hour->weekday_number] ?? 'Day ' . $hour->weekday_number }}</td>
                                <td>
                                    @if($hour->is_closed)
                                        Closed
                                    @else
                                        {{ substr($hour->open_time, 0, 5) }} - {{ substr($hour->close_time, 0, 5) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No working hours available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="panel">
            <div class="panel-head">
                <h2>Available Services</h2>
                <span class="badge">{{ $office->services->count() }} services</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Appointment</th>
                        <th>Payment</th>
                        <th>Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($office->services as $service)
                        <tr>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->category->name ?? '' }}</td>
                            <td>${{ number_format((float) $service->price, 2) }}</td>
                            <td>{{ $service->duration_minutes }} minutes</td>
                            <td>{{ $service->requires_appointment ? 'Required' : 'Not Required' }}</td>
                            <td>
                                {{ $service->supports_online_payment ? 'Card ' : '' }}
                                {{ $service->supports_crypto_payment ? 'Crypto' : '' }}

                                @if(!$service->supports_online_payment && !$service->supports_crypto_payment)
                                    Not available
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('discovery.services.show', $service->id) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No active services available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>