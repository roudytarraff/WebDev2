<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifications</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Notifications</h1>
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
            <section class="grid two">
                <div class="panel">
                    <h2>Create Staff Notification</h2>
                    <form method="POST" action="{{ route('office.notifications.store') }}">
                        @csrf
                        <div>
                            <label>User</label>
                            <select name="user_id">@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->full_name }}</option>@endforeach</select>
                        </div>
                        <div>
                            <label>Channel</label>
                            <select name="channel">
                                <option value="system">System</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                        <div>
                            <label>Title</label>
                            <input name="title">
                        </div>
                        <div>
                            <label>Message</label>
                            <textarea name="message">
                            </textarea>
                        </div>
                        <div>
                            <button>Create Notification</button>
                        </div>
                    </form>
                </div>
                <div class="panel">
                    <h2>Notifications</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Title</th>
                                <th>Channel</th>
                                <th>Read</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->user->full_name ?? '' }}</td>
                                    <td>{{ $notification->title }}<br>
                                        <small>{{ $notification->message }}</small>
                                    </td>
                                    <td>{{ $notification->channel }}</td>
                                    <td>{{ $notification->is_read ? 'Yes' : 'No' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('office.notifications.read', $notification->id) }}">@csrf
                                            @method('PUT')<button>Mark Read</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No notifications yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </body>
</html>