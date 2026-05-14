<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Office Profile</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Edit Office Profile</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.profile.show') }}" class="button secondary">Back</a>
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
            <div class="panel">
                <form method="POST" action="{{ route('office.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div>
                        <label>Office Name</label>
                        <input name="name" value="{{ old('name', $office->name) }}">
                    </div>
                    <div>
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $office->contact_email) }}">
                    </div>
                    <div>
                        <label>Contact Phone</label>
                        <input name="contact_phone" value="{{ old('contact_phone', $office->contact_phone) }}">
                    </div>
                    <div>
                        <label>Address Line 1</label>
                        <input name="address_line_1" value="{{ old('address_line_1', $office->address->address_line_1 ?? '') }}">
                    </div>
                    <div>
                        <label>Address Line 2</label>
                        <input name="address_line_2" value="{{ old('address_line_2', $office->address->address_line_2 ?? '') }}">
                    </div>
                    <div>
                        <label>City</label>
                        <input name="city" value="{{ old('city', $office->address->city ?? '') }}">
                    </div>
                    <div>
                        <label>Region</label>
                        <input name="region" value="{{ old('region', $office->address->region ?? '') }}">
                    </div>
                    <div>
                        <label>Country</label>
                        <input name="country" value="{{ old('country', $office->address->country ?? 'Lebanon') }}">
                    </div>
                    @include('partials.map_picker', [
                    'label' => 'Office Location',
                    'latitude' => $office->address->latitude ?? null,
                    'longitude' => $office->address->longitude ?? null,
                    ])
                    <div>
                        <button>Save Profile</button>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>