<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Users</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Users</h1>
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
                <div class="panel-head">
                    <h2>All Users</h2>
                    <a class="button" href="{{ route('admin.users.create') }}">Create User</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }}</td>
                                <td>
                                    <span class="badge">{{ $user->status }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.users.show', $user->id) }}">View</a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}">@csrf<button type="submit" class="secondary">Toggle</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">@csrf
                                        @method('DELETE')<button class="danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>