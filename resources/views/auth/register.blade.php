<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-body">

                    <h3 class="text-center mb-4">Register</h3>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.create') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name</label>
                                <input name="first_name" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Last Name</label>
                                <input name="last_name" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input name="email" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Phone</label>
                            <input name="phone" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <button class="btn btn-success w-100">Register</button>
                    </form>

                    <p class="text-center mt-3">
                        Already have account?
                        <a href="{{ route('auth.login') }}">Login</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>