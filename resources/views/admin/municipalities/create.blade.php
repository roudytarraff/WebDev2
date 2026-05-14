<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Municipality</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Create Municipality</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.municipalities.index') }}" class="button secondary">Back</a>
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
            <form method="POST" action="{{ route('admin.municipalities.store') }}">@csrf
                @if(isset($municipality)) @method('PUT') @endif
                    <div>
                        <label>Name</label>
                        <input name="name" value="{{ old('name', $municipality->name ?? '') }}">
                    </div>
                    <div>
                        <label>Region</label>
                        <input name="region" value="{{ old('region', $municipality->region ?? '') }}">
                    </div>
                    <div>
                        <label>Address Line 1</label>
                        <input name="address_line_1" value="{{ old('address_line_1', $municipality->address->address_line_1 ?? '') }}">
                    </div>
                    <div>
                        <label>Address Line 2</label>
                        <input name="address_line_2" value="{{ old('address_line_2', $municipality->address->address_line_2 ?? '') }}">
                    </div>
                    <div>
                        <label>City</label>
                        <input name="city" value="{{ old('city', $municipality->address->city ?? '') }}">
                    </div>
                    <div>
                        <label>Country</label>
                        <input name="country" value="{{ old('country', $municipality->address->country ?? 'Lebanon') }}">
                    </div>
                    @include('partials.map_picker', [
                    'label' => 'Municipality Location',
                    'latitude' => $municipality->address->latitude ?? null,
                    'longitude' => $municipality->address->longitude ?? null,
                    ])
                    <div>
                        <label>Status</label>
                        <select name="status">
                            @foreach(['active', 'inactive'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $municipality->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit">Save Municipality</button>
                    </div>
                </form>
            </main>
        </body>
    </html>