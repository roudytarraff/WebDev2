<!DOCTYPE html>

<html>
<head>
   
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('css/app2.css') }}">
</head>
<body>

    <main>

        <h1>Login</h1>


        <form action="{{ route('auth.dologin') }}" method="post">
            @csrf

            <label for="email">Email</label>
            <input type="text" name="email" value="{{ old('email') }}">
            @error('email') <p>{{ $message }}</p>@enderror

            <label for="password">Password</label>
            <input type="password" name="password" value="{{ old('password') }}">
            @error('password') <p>{{ $message }}</p>@enderror

            <button type="submit">Login</button>

        </form>

        <a href="{{ route('auth.register') }}">Don't have an account? Register</a>

    </main>


    
</body>


<script>



    
</script>
</html>