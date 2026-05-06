<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>User Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.users.index') }}" class="button secondary">Back</a>
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
                <p>
                    <strong>Email:</strong> {{ $user->email }}</p>
                    <p>
                        <strong>Phone:</strong> {{ $user->phone ?? 'Not set' }}</p>
                        <p>
                            <strong>Status:</strong> <span class="badge">{{ $user->status }}</span>
                        </p>
                        <p>
                            <strong>Roles:</strong> {{ $user->roles->pluck('name')->implode(', ') ?: 'No roles' }}</p>
                            <p>
                                <strong>Office assignments:</strong> {{ $user->officeStaff->pluck('office.name')->implode(', ') ?: 'None' }}</p>
                                <div class="actions">
                                    <a class="button" href="{{ route('admin.users.edit', $user->id) }}">Edit</a>
                                    <a class="button secondary" href="{{ route('admin.users.index') }}">Back</a>
                                </div>
                            </div>
                        </main>
                    </body>
                </html>