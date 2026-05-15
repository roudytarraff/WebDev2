<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>

        @if(auth()->user()?->isAdmin())
            @include('admin.navbar')
        @elseif(auth()->user()?->isOfficeStaff())
            @include('office.navbar')
        @else
            @include('citizen.navbar')
        @endif

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Home</h1>
                    <p>{{ auth()->user()?->full_name }}</p>
                </div>
            </header>

            <div class="panel">
                <div class="right">
                    <h1>{{ auth()->user()?->full_name }}</h1>
                    <h3>{{ auth()->user()?->email }}</h3>
                    <h3>Status: {{ auth()->user()?->status }}</h3>

                    <div>
                        @if(auth()->user()?->isAdmin())
                            <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                        @endif

                        @if(auth()->user()?->isOfficeStaff())
                            <a href="{{ route('office.dashboard') }}">Office Dashboard</a>
                        @endif

                        @if(!auth()->user()?->isAdmin() && !auth()->user()?->isOfficeStaff())
                            <a href="{{ route('chat.index') }}" class="button">My Chats</a>
                            <a href="{{ route('chat.create') }}" class="button">Start a Chat</a>
                        @endif
                    </div>
                </div>
            </div>
        </main>

    </body>
</html>
