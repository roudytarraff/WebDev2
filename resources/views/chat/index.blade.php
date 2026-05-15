<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>
<body>
    @if(auth()->user()?->isOfficeStaff())
        @include('office.navbar')
    @else
        @include('citizen.navbar')
    @endif

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Chats</h1>
                <p>{{ Auth::user()->full_name }}</p>
            </div>
        </header>

        <div class="back-row">
            <a href="{{ url()->previous() }}" class="button secondary">Back</a>
            @if(!auth()->user()->isOfficeStaff())
                <a href="{{ route('chat.create') }}" class="button">Start New Chat</a>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>{{ $office ? 'Office Conversations' : 'My Conversations' }}</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        @if($office)
                            <th>Citizen</th>
                        @endif
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
                            @if($office)
                                <td>{{ $chat->citizen->full_name ?? '—' }}</td>
                            @endif
                            <td>{{ $chat->request->request_number ?? '—' }}</td>
                            <td>{{ $chat->request->service->name ?? '—' }}</td>
                            <td>{{ $chat->office->name ?? '—' }}</td>
                            <td><span class="badge">{{ $chat->status }}</span></td>
                            <td>{{ $chat->messages->count() }}</td>
                            <td>
                                <a href="{{ route('chat.show', $chat->id) }}" class="button">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $office ? 7 : 6 }}">
                                No chats yet.
                                @if(!auth()->user()->isOfficeStaff())
                                    <a href="{{ route('chat.create') }}">Start one now.</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
