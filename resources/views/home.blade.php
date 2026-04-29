<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 text-center">

    <div class="card shadow p-5">
        <h1>Home Page</h1>
        <p class="text-muted">User dashboard placeholder</p>

        <a href="{{ route('auth.logout') }}" class="btn btn-danger">
            Logout
        </a>
    </div>

</div>

</body>
</html>