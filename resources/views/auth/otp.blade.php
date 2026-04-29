<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-body">

                    <h4 class="text-center">Verify OTP</h4>

                    <p class="text-muted text-center">
                        We sent a code to your email
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf

                        <input name="otp" class="form-control text-center mb-3" placeholder="6-digit code">

                        <button class="btn btn-primary w-100">Verify</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>