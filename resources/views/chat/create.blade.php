<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start a Chat</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>
<body>
    @include('citizen.navbar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Start a Chat</h1>
                <p>{{ Auth::user()->full_name }}</p>
            </div>
        </header>

        <div class="back-row">
            <a href="{{ route('chat.index') }}" class="button secondary">Back to Chats</a>
        </div>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        <div class="panel">
            <div class="panel-head">
                <h2>Select a Service Request to Chat About</h2>
            </div>

            @if($requests->isEmpty())
                <p class="muted" style="padding:1rem;">You have no service requests yet. Submit a request first to open a chat with the office.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Service</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td>{{ $req->request_number }}</td>
                                <td>{{ $req->service->name ?? '—' }}</td>
                                <td>{{ $req->office->name ?? '—' }}</td>
                                <td><span class="badge">{{ $req->status }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('chat.store') }}">
                                        @csrf
                                        <input type="hidden" name="request_id" value="{{ $req->id }}">
                                        <button type="submit" class="button">Open Chat</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</body>
</html>
