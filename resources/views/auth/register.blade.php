<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            <section class="auth-hero">
                <div>
                    <div class="auth-brand-mark">ES</div>
                    <span class="auth-kicker">Citizen Access</span>
                    <h1>Start your digital services account</h1>
                    <p>Create one account to request municipal services, upload documents, track status, and receive updates.</p>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-card">

                    <div class="auth-card-header">
                        <h2>Create your account</h2>
                        <p>Register once, then verify your email with the one-time passcode.</p>
                    </div>

                    <form method="POST" action="{{ route('auth.create') }}" class="resource-form">
                        @csrf

                        <div class="form-grid">
                            <div class="form-field">
                                <label class="form-label">First Name</label>
                                <input class="form-control" name="first_name" value="{{ old('first_name') }}">
                                @error('first_name') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Last Name</label>
                                <input class="form-control" name="last_name" value="{{ old('last_name') }}">
                                @error('last_name') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Email</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                                @error('email') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Phone</label>
                                <input class="form-control" name="phone" value="{{ old('phone') }}">
                                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Password</label>
                                <input class="form-control" type="password" name="password">
                                @error('password') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-field">
                                <label class="form-label">Confirm Password</label>
                                <input class="form-control" type="password" name="password_confirmation">
                            </div>
                        </div>

                        <button class="admin-primary-button auth-primary-button">Create account</button>
                    </form>

                    <p class="auth-footer-link">
                        Already have an account?
                        <a href="{{ route('auth.login') }}">Sign in</a>
                    </p>
                </div>
            </section>
        </div>
    </body>
</html>
