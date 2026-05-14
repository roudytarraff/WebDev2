<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Office</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Create Office</h1>
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
            <form method="POST" action="{{ route('admin.offices.store') }}">@csrf
                @if(isset($office)) @method('PUT') @endif
                    <div>
                        <label>Municipality</label>
                        <select name="municipality_id">@foreach($municipalities as $municipality)<option value="{{ $municipality->id }}" @selected(old('municipality_id', $office->municipality_id ?? '') == $municipality->id)>{{ $municipality->name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label>Office Name</label>
                        <input name="name" value="{{ old('name', $office->name ?? '') }}">
                    </div>
                    <div>
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $office->contact_email ?? '') }}">
                    </div>
                    <div>
                        <label>Contact Phone</label>
                        <input name="contact_phone" value="{{ old('contact_phone', $office->contact_phone ?? '') }}">
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
                        <label>Status</label>
                        <select name="status">@foreach(['active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $office->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                    </div>
                    <div>
                        <button type="submit">Save Office</button>
                    </div>
                </form>
            </main>
        </body>
    </html>