<div>
    <nav>
    
        <a href="#">Add Later</a>
    


        @guest
            <a href="{{ route('auth.login') }}">Login</a>
        @endguest

        @auth
            <a href="{{ route('auth.logout') }}">Logout</a>
        @endauth
    </nav>
</div>