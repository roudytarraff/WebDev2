<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Working Hours</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Working Hours</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>

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
            @php
                $dayNames = [
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    7 => 'Sunday',
                ];
            @endphp
            <div class="panel">
                <div class="panel-head">
                    <h2>{{ $office->name }}</h2>
                    <a class="button" href="{{ route('office.working-hours.create') }}">Add Day</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Open</th>
                            <th>Close</th>
                            <th>Closed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hours as $hour)
                            <tr>
                                <td>{{ $dayNames[$hour->weekday_number] ?? 'Day ' . $hour->weekday_number }}</td>
                                <td>{{ $hour->open_time }}</td>
                                <td>{{ $hour->close_time }}</td>
                                <td>
                                    <span class="badge">{{ $hour->is_closed ? 'Yes' : 'No' }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('office.working-hours.edit', $hour->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('office.working-hours.destroy', $hour->id) }}">@csrf
                                        @method('DELETE')<button class="danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>
