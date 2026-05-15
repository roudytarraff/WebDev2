@auth<script>window.__userId = {{ Auth::id() }};</script>@endauth
<input type="checkbox" id="navToggle" class="nav-toggle">
<label for="navToggle" class="hamburger">Menu</label>

<aside class="sidebar">
    @auth
        <a class="brand" href="{{ route('office.dashboard') }}">Municipality Office</a>

        <a href="{{ route('office.dashboard') }}">Dashboard</a>
        <a href="{{ route('office.profile.show') }}">Office Profile</a>
        <a href="{{ route('office.working-hours.index') }}">Working Hours</a>
        <a href="{{ route('office.categories.index') }}">Categories</a>
        <a href="{{ route('office.document-types.index') }}">Document Types</a>
        <a href="{{ route('office.services.index') }}">Services</a>
        <a href="{{ route('office.requests.index') }}">Requests</a>
        <a href="{{ route('office.appointment-slots.index') }}">Slots</a>
        <a href="{{ route('office.appointments.index') }}">Appointments</a>
        <a href="{{ route('office.feedback.index') }}">Feedback</a>
        <a href="{{ route('office.chats.index') }}">Chats</a>
        <a href="{{ route('office.payments.index') }}">Payments</a>
        <a href="{{ route('office.notifications.index') }}" style="position:relative;">
            Notifications
            @php $unread = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count(); @endphp
            @if($unread > 0)
                <span id="notifBadge" style="background:#ef4444;color:#fff;font-size:11px;border-radius:50%;padding:1px 6px;margin-left:4px;">{{ $unread }}</span>
            @else
                <span id="notifBadge" class="hidden" style="background:#ef4444;color:#fff;font-size:11px;border-radius:50%;padding:1px 6px;margin-left:4px;display:none;">0</span>
            @endif
        </a>

        <a href="{{ route('auth.logout') }}">Logout</a>
    @endauth
</aside>
