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
                <div class="alert error">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="grid two">

                {{-- Send notification to staff --}}
                <div class="panel">
                    <h2>Send Staff Notification</h2>
                    <form method="POST" action="{{ route('office.notifications.store') }}">
                        @csrf
                        <div>
                            <label>Staff Member</label>
                            <select name="user_id">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Delivery Channel</label>
                            <select name="channel">
                                <option value="system">In-app only</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                        <div>
                            <label>Title</label>
                            <input name="title" value="{{ old('title') }}">
                        </div>
                        <div>
                            <label>Message</label>
                            <textarea name="message">{{ old('message') }}</textarea>
                        </div>
                        <div>
                            <button>Send Notification</button>
                        </div>
                    </form>
                </div>

                {{-- Current user's notifications --}}
                <div class="panel">
                    <div class="panel-head">
                        <h2>
                            My Notifications
                            @if($unreadCount > 0)
                                <span style="background:#ef4444;color:#fff;font-size:12px;border-radius:20px;padding:2px 10px;margin-left:6px;">{{ $unreadCount }} unread</span>
                            @endif
                        </h2>
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('office.notifications.mark-all-read') }}" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <button class="button secondary" style="font-size:12px;padding:4px 12px;">Mark all read</button>
                            </form>
                        @endif
                    </div>

                    <div id="notificationList">
                        @forelse($notifications as $notif)
                            <div
                                class="notification-item {{ $notif->is_read ? '' : 'unread' }}"
                                data-id="{{ $notif->id }}"
                                style="padding:12px;border-bottom:1px solid #e5e7eb;{{ $notif->is_read ? '' : 'background:#eff6ff;' }}"
                            >
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                                    <div>
                                        <strong>{{ $notif->title }}</strong>
                                        <br>
                                        <small style="color:#6b7280;">{{ $notif->message }}</small>
                                        <br>
                                        <small style="color:#9ca3af;">{{ $notif->created_at->diffForHumans() }} &middot; {{ $notif->channel }}</small>
                                    </div>
                                    @if(! $notif->is_read)
                                        <form method="POST" action="{{ route('office.notifications.read', $notif->id) }}" style="margin:0;flex-shrink:0;">
                                            @csrf
                                            @method('PUT')
                                            <button class="button secondary" style="font-size:11px;padding:3px 10px;">Read</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="muted" id="emptyNotifs" style="padding:16px;">No notifications yet.</p>
                        @endforelse
                    </div>
                </div>

            </section>
        </main>

        @vite(['resources/js/app.js'])

        <script>
            const myUserId    = {{ Auth::id() }};
            const list        = document.getElementById('notificationList');
            const badge       = document.getElementById('notifBadge');
            let   unreadCount = {{ $unreadCount }};

            function updateBadge(delta) {
                unreadCount = Math.max(0, unreadCount + delta);
                if (badge) {
                    badge.textContent = unreadCount;
                    badge.style.display = unreadCount > 0 ? '' : 'none';
                }
            }

            function prependNotification(data) {
                document.getElementById('emptyNotifs')?.remove();

                const item = document.createElement('div');
                item.className = 'notification-item unread';
                item.dataset.id = data.id;
                item.style.cssText = 'padding:12px;border-bottom:1px solid #e5e7eb;background:#eff6ff;';
                item.innerHTML = `
                    <strong>${data.title}</strong><br>
                    <small style="color:#6b7280;">${data.message}</small><br>
                    <small style="color:#9ca3af;">just now &middot; system</small>
                `;
                list.prepend(item);
                updateBadge(+1);
            }

            if (window.Echo) {
                window.Echo.private(`user.${myUserId}`)
                    .listen('.notification.sent', (data) => {
                        prependNotification(data);
                    });
            }
        </script>
    </body>
</html>
