<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>

    <body>
        @if(auth()->user()?->isAdmin())
            @include('admin.navbar')
        @elseif(auth()->user()?->isOfficeStaff())
            @include('office.navbar')
        @else
            @include('citizen.navbar')
        @endif

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Home</h1>
                    <p>{{ auth()->user()?->full_name }}</p>
                </div>
            </header>

            @php
                $citizenProfile = auth()->user()?->citizenProfile;
                $isVerifiedCitizen = $citizenProfile && $citizenProfile->verification_status === 'verified';
            @endphp

            <div class="panel">
                <div class="right">
                    <h1>{{ auth()->user()?->full_name }}</h1>
                    <h3>{{ auth()->user()?->email }}</h3>
                    <h3>Status: {{ auth()->user()?->status }}</h3>

                    @if(!auth()->user()?->isAdmin() && !auth()->user()?->isOfficeStaff())
                        <h3>
                            Citizen Verification:
                            @if($isVerifiedCitizen)
                                <span class="badge">Verified Citizen</span>
                            @elseif($citizenProfile && $citizenProfile->verification_status === 'rejected')
                                <span class="badge">Rejected</span>
                            @else
                                <span class="badge">Not Verified</span>
                            @endif
                        </h3>

                        @if(!$isVerifiedCitizen)
                            <div class="alert error">
                                Your citizen profile is not verified. Please verify your profile before using citizen portal features.
                            </div>
                        @endif
                    @endif

                    <div class="actions">
                        <a class="button" href="{{ route('discovery.index') }}">Discover Services</a>

                        @if(auth()->user()?->isAdmin())
                            <a class="button" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                        @elseif(auth()->user()?->isOfficeStaff())
                            <a class="button" href="{{ route('office.dashboard') }}">Office Dashboard</a>
                        @else
                            <a class="button" href="{{ route('citizen.profile.show') }}">My Citizen Profile</a>

                            @if($isVerifiedCitizen)
                                <a class="button" href="{{ route('citizen.requests.index') }}">My Requests</a>
                            @else
                                <a class="button" href="{{ route('citizen.profile.edit') }}">Verify Profile</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>