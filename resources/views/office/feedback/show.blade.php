<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feedback Reply</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Feedback Reply</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.feedback.index') }}" class="button secondary">Back</a>
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
            <section class="grid two">
                <div class="panel">
                    <h2>Feedback</h2>
                    <p>
                        <strong>Request:</strong> {{ $feedback->request->request_number ?? '' }}</p>
                        <p>
                            <strong>Service:</strong> {{ $feedback->request->service->name ?? '' }}</p>
                            <p>
                                <strong>Rating:</strong> {{ $feedback->rating }}/5</p>
                                <p>{{ $feedback->comment }}</p>
                            </div>
                            <div class="panel">
                                <h2>Office Reply</h2>
                                <form method="POST" action="{{ route('office.feedback.reply', $feedback->id) }}">@csrf
                                    @method('PUT')<div>
                                    <textarea name="office_reply">{{ old('office_reply', $feedback->office_reply) }}</textarea>
                                </div>
                                <button>Save Reply</button>
                            </form>
                        </section>
                    </main>
                </body>
            </html>