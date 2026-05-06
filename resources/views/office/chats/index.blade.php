<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chats</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Chats</h1>
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
            <div class="panel">
                <table>
                    <thead>
                        <tr>
                            <th>Citizen</th>
                            <th>Request</th>
                            <th>Messages</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($chats as $chat)<tr>
                        <td>{{ $chat->citizen->full_name ?? '' }}</td>
                        <td>{{ $chat->request->request_number ?? '' }}</td>
                        <td>{{ $chat->messages->count() }}</td>
                        <td>
                            <span class="badge">{{ $chat->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('office.chats.show', $chat->id) }}">Open</a>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="5">No chats yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>