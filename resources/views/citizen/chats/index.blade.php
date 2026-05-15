<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>
<body>
    @include('citizen.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>My Chats</h1>
                <p>{{ Auth::user()->full_name }}</p>
            </div>
        </header>

        <div class="back-row">
            <a href="{{ route('chat.create') }}" class="button">Start New Chat</a>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Active Conversations</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Messages</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chats as $chat)
                        <tr>
                            <td>{{ $chat->request->request_number ?? '' }}</td>
                            <td>{{ $chat->request->service->name ?? '' }}</td>
                            <td>{{ $chat->office->name ?? '' }}</td>
                            <td><span class="badge">{{ $chat->status }}</span></td>
                            <td>{{ $chat->messages->count() }}</td>
                            <td>
                                <a href="{{ route('chat.show', $chat->id) }}" class="button">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                No chats yet. <a href="{{ route('chat.create') }}">Start one now.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
