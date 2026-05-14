<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Working Hours</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Add Working Hours</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.working-hours.index') }}" class="button secondary">Back</a>
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
            <form method="POST" action="{{ route('office.working-hours.store') }}">@csrf
                @if(isset($hour)) @method('PUT') @endif
                    <div>
                        <label>Day</label>
                        <select name="weekday_number">
                            @foreach([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'] as $i => $day)
                                <option value="{{ $i }}" @selected(old('weekday_number', $hour->weekday_number ?? '') == $i)>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Open Time</label>
                        <input type="time" name="open_time" value="{{ old('open_time', isset($hour) ? substr($hour->open_time, 0, 5) : '08:00') }}">
                    </div>
                    <div>
                        <label>Close Time</label>
                        <input type="time" name="close_time" value="{{ old('close_time', isset($hour) ? substr($hour->close_time, 0, 5) : '15:00') }}">
                    </div>
                    <div>
                        <label>Closed Day</label>
                        <select name="is_closed">
                            <option value="0" @selected(!old('is_closed', $hour->is_closed ?? false))>No</option>
                            <option value="1" @selected(old('is_closed', $hour->is_closed ?? false))>Yes</option>
                        </select>
                    </div>
                    <div>
                        <button>Save Working Hours</button>
                    </div>
                </form>
            </main>
        </body>
    </html>
