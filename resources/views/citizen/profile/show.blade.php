<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Profile</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
    @include('citizen.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Citizen Profile</h1>
                <p>View your personal information and verification status.</p>
            </div>

            <a href="{{ route('citizen.profile.edit') }}" class="button">Verify / Update Profile</a>
        </header>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @php
            $isVerifiedCitizen = $profile && $profile->verification_status === 'verified';
        @endphp

        <section class="stats-grid">
            <div class="stat">
                <span>Verification Status</span>

                @if($isVerifiedCitizen)
                    <strong>Verified</strong>
                @elseif($profile && $profile->verification_status === 'rejected')
                    <strong>Rejected</strong>
                @else
                    <strong>Not Verified</strong>
                @endif
            </div>

            <div class="stat">
                <span>Name</span>
                <strong>{{ $user->full_name }}</strong>
            </div>

            <div class="stat">
                <span>Email</span>
                <strong>{{ $user->email }}</strong>
            </div>
        </section>

        @if(! $isVerifiedCitizen)
            <div class="alert error">
                Your citizen profile is not verified. You must verify your profile before using citizen portal features such as My Requests, appointment booking, payments, and notifications.
            </div>
        @endif

        <section class="grid two">
            <div class="panel">
                <h2>Personal Information</h2>

                <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone ?? 'Not added yet' }}</p>
                <p><strong>National ID Number:</strong> {{ $profile->national_id_number ?? 'Not added yet' }}</p>
                <p><strong>Date of Birth:</strong> {{ $profile->date_of_birth ?? 'Not detected yet' }}</p>

                <p>
                    <strong>Status:</strong>

                    @if($isVerifiedCitizen)
                        <span class="badge">Verified Citizen</span>
                    @elseif($profile && $profile->verification_status === 'rejected')
                        <span class="badge">Rejected</span>
                    @else
                        <span class="badge">Not Verified</span>
                    @endif
                </p>

                @if($profile && $profile->id_document_path)
                    <p>
                        <strong>ID Document:</strong>
                        <a href="{{ route('citizen.profile.id-document') }}" target="_blank">
                            View uploaded ID
                        </a>
                    </p>
                @else
                    <p><strong>ID Document:</strong> Not uploaded yet</p>
                @endif
            </div>

            <div class="panel">
                <h2>Address</h2>

                @if($profile && $profile->address)
                    <p><strong>Address Line 1:</strong> {{ $profile->address->address_line_1 }}</p>

                    @if($profile->address->address_line_2)
                        <p><strong>Address Line 2:</strong> {{ $profile->address->address_line_2 }}</p>
                    @endif

                    <p><strong>City:</strong> {{ $profile->address->city }}</p>
                    <p><strong>Region:</strong> {{ $profile->address->region }}</p>
                    <p><strong>Postal Code:</strong> {{ $profile->address->postal_code ?? 'Not added' }}</p>
                    <p><strong>Country:</strong> {{ $profile->address->country }}</p>
                @else
                    <p>No address added yet.</p>
                @endif
            </div>
        </section>

        <div class="panel">
            @if($isVerifiedCitizen)
                <p>Your profile is verified. You can now use citizen portal features.</p>
                <a href="{{ route('citizen.requests.index') }}" class="button">Go to My Requests</a>
            @else
                <p>You need to verify your profile before using citizen portal features.</p>
                <a href="{{ route('citizen.profile.edit') }}" class="button">Verify Profile</a>
            @endif
        </div>
    </main>
</body>
</html>