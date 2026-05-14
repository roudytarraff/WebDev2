<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit User</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Edit User</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.users.index') }}" class="button secondary">Back</a>
            </div>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div class="panel">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    @if(isset($user)) @method('PUT') @endif
                        <div>
                            <label>First name</label>
                            <input name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}">
                        </div>
                        <div>
                            <label>Last name</label>
                            <input name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}">
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                        </div>
                        <div>
                            <label>Password</label>
                            <input type="password" name="password">
                        </div>
                        <div>
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation">
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                @foreach(['active', 'inactive', 'pending'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $user->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Roles</label>
                            <select name="roles[]" multiple>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(collect(old('roles', isset($user) ? $user->roles->pluck('id')->toArray() : []))->contains($role->id))>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit">Save User</button>
                        </div>

                    </form>
                </div>
            </main>
        </body>
    </html>