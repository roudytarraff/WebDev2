<!DOCTYPE html>

<html>
<head>
   
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('css/app2.css') }}">
</head>
<body>

    <main>

        <h1>Register</h1>


        <form action="{{ route('auth.doregister') }}" method="post">
            @csrf

            <label for="name">Name</label>
            <input type="text" name="name" value="{{ old('name') }}">
            @error('name') <p>{{ $message }}</p>@enderror

            <label for="email">Email</label>
            <input type="text" name="email" value="{{ old('email') }}">
            @error('email') <p>{{ $message }}</p>@enderror

            <label for="password">Password</label>
            <input type="password" name="password" value="{{ old('password') }}">
            @error('password') <p>{{ $message }}</p>@enderror

            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
            @error('password_confirmation') <p>{{ $message }}</p>@enderror

            <button type="submit">Add</button>

        </form>

        <a href="{{ route('auth.login') }}">Already have an account? Login</a>
    </main>


    
</body>


<script>



    
</script>
</html>