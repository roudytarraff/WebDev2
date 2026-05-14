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
        <a href="{{ route('office.notifications.index') }}">Notifications</a>

        <a href="{{ route('auth.logout') }}">Logout</a>
    @endauth
</aside>
