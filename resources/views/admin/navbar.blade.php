<input type="checkbox" id="navToggle" class="nav-toggle">
<label for="navToggle" class="hamburger">Menu</label>

<aside class="sidebar">
    @auth
        <a class="brand" href="{{ route('admin.dashboard') }}">Admin Panel</a>

        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.users.index') }}">Users</a>
        <a href="{{ route('admin.municipalities.index') }}">Municipalities</a>
        <a href="{{ route('admin.offices.index') }}">Offices</a>
        <a href="{{ route('admin.office-staff.index') }}">Office Staff</a>
        <a href="{{ route('admin.reports.index') }}">Reports</a>

        <a href="{{ route('auth.logout') }}">Logout</a>
    @endauth

    @guest
        <a class="brand" href="{{ route('auth.login') }}">E-Services</a>
        <a href="{{ route('auth.login') }}">Login</a>
        <a href="{{ route('auth.register') }}">Register</a>
    @endguest
</aside>
