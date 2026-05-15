<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Office Staff</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Office Staff</h1>
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
                    <h2>Assignments</h2>
                    <a class="button" href="{{ route('admin.office-staff.create') }}">Assign Staff</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Office</th>
                            <th>Job Title</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td>{{ $member->user->full_name }}</td>
                                <td>{{ $member->office->name }}</td>
                                <td>{{ $member->job_title }}</td>
                                <td>
                                    <span class="badge">{{ $member->status }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.office-staff.show', $member->id) }}">View</a>
                                    <a href="{{ route('admin.office-staff.edit', $member->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.office-staff.toggle-status', $member->id) }}">@csrf<button>Toggle</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.office-staff.destroy', $member->id) }}">@csrf
                                        @method('DELETE')<button class="danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No staff assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>