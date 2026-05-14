<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify OTP</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            <section class="auth-hero">
                <div>
                    <div class="auth-brand-mark">ES</div>
                    <span class="auth-kicker">Security Check</span>
                    <h1>One more quick check</h1>
                    <p>OTP verification keeps public service records protected before opening the platform.</p>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-card">
                    <div class="back-row">
                        <a href="{{ route('auth.login') }}" class="button secondary">Back</a>
                    </div>

                    <div class="auth-card-header">
                        <h2>Verify your one-time code</h2>
                        <p>Enter the 6-digit code sent to your email.</p>
                    </div>

                    @if(session('demo_otp'))
                        <div class="admin-alert admin-alert-success">
                            Demo OTP: <strong>{{ session('demo_otp') }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('otp.verify') }}" class="resource-form">
                        @csrf

                        <div class="form-grid form-grid-single">
                            <div class="form-field">
                                <label class="form-label">OTP</label>
                                <input class="form-control" name="otp" value="{{ old('otp') }}">
                                @error('otp')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button class="admin-primary-button auth-primary-button">Verify code</button>
                    </form>
                </div>
            </section>
        </div>
    </body>
</html>
