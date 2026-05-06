<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            <section class="auth-hero">
                <div>
                    <div class="auth-brand-mark">ES</div>
                    <span class="auth-kicker">Municipal Services</span>
                    <h1>Public services, handled in one place</h1>
                    <p>Manage office requests, appointments, payments, feedback, and citizen communication from one secure dashboard.</p>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-card">

                    <div class="auth-card-header">
                        <h2>Sign in</h2>
                        <p>Use your municipal account to continue.</p>
                    </div>

                    <form method="POST" action="{{ route('auth.connect') }}" class="resource-form">
                        @csrf

                        <div class="form-grid form-grid-single">
                            <div class="form-field">
                                <label class="form-label">Email</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Password</label>
                                <input class="form-control" type="password" name="password">
                                @error('password')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="auth-remember">
                                <input type="checkbox" name="remember" value="1" checked>
                                Remember me on this browser
                            </label>
                        </div>

                        <button class="admin-primary-button auth-primary-button">Sign in</button>
                    </form>

                    <div class="auth-divider"><span>or</span></div>

                    <a href="{{ route('google.redirect') }}" class="admin-ghost-button auth-full-button">Continue with Google</a>

                    <p class="auth-footer-link">
                        No account yet?
                        <a href="{{ route('auth.register') }}">Create one</a>
                    </p>
                </div>
            </section>
        </div>
    </body>
</html>
