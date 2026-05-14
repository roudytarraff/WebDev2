<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Staff Assignment</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Staff Assignment</h1>
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
            <div class="panel">
                <p>
                    <strong>Office:</strong> {{ $staff->office->name }}</p>
                    <p>
                        <strong>Municipality:</strong> {{ $staff->office->municipality->name ?? '' }}</p>
                        <p>
                            <strong>Job title:</strong> {{ $staff->job_title }}</p>
                            <p>
                                <strong>Status:</strong> <span class="badge">{{ $staff->status }}</span>
                            </p>
                            <p>
                                <strong>User roles:</strong> {{ $staff->user->roles->pluck('name')->implode(', ') }}</p>
                            </div>
                        </main>
                    </body>
                </html>