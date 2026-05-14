<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Staff Assignment</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Edit Staff Assignment</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('admin.office-staff.index') }}" class="button secondary">Back</a>
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
            <form method="POST" action="{{ route('admin.office-staff.update', $staff->id) }}">@csrf
                @if(isset($staff)) @method('PUT') @endif
                    <div>
                        <label>Office</label>
                        <select name="office_id">@foreach($offices as $office)<option value="{{ $office->id }}" @selected(old('office_id', $staff->office_id ?? '') == $office->id)>{{ $office->name }}</option>@endforeach</select>
                    </div>

                    @if(!isset($staff))
                        <div>
                            <label>User Source</label>
                            <select name="user_mode" id="staffUserMode">
                                <option value="existing" @selected(old('user_mode', 'existing') === 'existing')>Choose existing user</option>
                                <option value="new" @selected(old('user_mode') === 'new')>Create new staff login</option>
                            </select>
                        </div>
                    @endif

                    <div class="field existing-user-field">
                        <label>User From Database</label>
                        <select name="user_id">
                            <option value="">Select user</option>
                            @foreach($users as $user)
                                @php
                                $roles = $user->roles->pluck('name')->implode(', ') ?: 'no role';
                                $offices = $user->officeStaff->pluck('office.name')->filter()->implode(', ');
                                @endphp
                                <option value="{{ $user->id }}" @selected(old('user_id', $staff->user_id ?? '') == $user->id)>
                                    {{ $user->full_name }} - {{ $user->email }} - {{ $user->status }} - {{ $roles }}{{ $offices ? ' - assigned: '.$offices : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="muted">This list is loaded from the users table.</p>
                    </div>
                    <div>
                        <label>Job Title</label>
                        <input name="job_title" value="{{ old('job_title', $staff->job_title ?? '') }}">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">@foreach(['active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $staff->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                    </div>

                    <div class="field new-user-field">
                        <label>First Name</label>
                        <input name="first_name" value="{{ old('first_name', $staff->user->first_name ?? '') }}">
                    </div>
                    <div class="field new-user-field">
                        <label>Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $staff->user->last_name ?? '') }}">
                    </div>
                    <div class="field new-user-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $staff->user->email ?? '') }}">
                    </div>
                    <div class="field new-user-field">
                        <label>Phone</label>
                        <input name="phone" value="{{ old('phone', $staff->user->phone ?? '') }}">
                    </div>
                    <div class="field new-user-field">
                        <label>Password</label>
                        <input type="password" name="password">
                    </div>
                    <div class="field new-user-field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation">
                    </div>

                    <div>
                        <button type="submit">Save Assignment</button>
                    </div>

                    @if(!isset($staff))
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                            const mode = document.getElementById('staffUserMode');
                            const existingFields = document.querySelectorAll('.existing-user-field');
                            const newFields = document.querySelectorAll('.new-user-field');

                            function toggleFields() {
                            const creating = mode.value === 'new';
                            existingFields.forEach(field => field.style.display = creating ? 'none' : '');
                            newFields.forEach(field => field.style.display = creating ? '' : 'none');
                            }

                            mode.addEventListener('change', toggleFields);
                            toggleFields();
                            });
                        </script>
                    @endif
                </form>
            </main>
        </body>
    </html>