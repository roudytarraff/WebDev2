@php
    $citizenProfile = auth()->user()?->citizenProfile;
    $isVerifiedCitizen = $citizenProfile && $citizenProfile->verification_status === 'verified';
@endphp

<input type="checkbox" id="citizen-nav-toggle" class="nav-toggle">
<label for="citizen-nav-toggle" class="hamburger">Citizen Menu</label>

<aside class="sidebar">
    <a class="brand" href="{{ route('home') }}">Citizen Portal</a>

    <a href="{{ route('home') }}">Home</a>
    <a href="{{ route('discovery.index') }}">Discover Services</a>
    <a href="{{ route('citizen.profile.show') }}">My Citizen Profile</a>

    @if($isVerifiedCitizen)
        <a href="{{ route('citizen.requests.index') }}">My Requests</a>

        @if(Route::has('citizen.payments.index'))
            <a href="{{ route('citizen.payments.index') }}">Payment History</a>
        @endif

        <a href="{{ route('citizen.feedback.index') }}">My Feedback</a>
    @else
        <a href="{{ route('citizen.profile.edit') }}">Verify Profile</a>
    @endif

    <a href="{{ route('auth.logout') }}">Logout</a>
</aside>