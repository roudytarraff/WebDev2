@auth<script>window.__userId = {{ Auth::id() }};</script>@endauth
<input type="checkbox" id="navToggle" class="nav-toggle">
<label for="navToggle" class="hamburger">Menu</label>

<aside class="sidebar">
    @auth
        <a class="brand" href="{{ route('home') }}">Citizen Portal</a>

        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('chat.index') }}">My Chats</a>

        <a href="{{ route('auth.logout') }}">Logout</a>
    @endauth
</aside>
