<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Feedback</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .feedback-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            background: #ffffff;
        }

        .feedback-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .stars {
            color: #f59e0b;
            font-size: 18px;
            letter-spacing: 1px;
        }

        .reply-box {
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        @media (max-width: 700px) {
            .feedback-head {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>My Feedback</h1>
            <p>View your ratings, comments, and office replies.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="alert success">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="panel">
        <div class="panel-head">
            <h2>Submitted Feedback</h2>
            <span class="badge">{{ $feedback->count() }} records</span>
        </div>

        @forelse($feedback as $item)
            <div class="feedback-card">
                <div class="feedback-head">
                    <div>
                        <h3>{{ $item->request->service->name ?? 'Service unavailable' }}</h3>
                        <p class="muted">
                            Request:
                            <a href="{{ route('citizen.requests.show', $item->request_id) }}">
                                {{ $item->request->request_number ?? 'N/A' }}
                            </a>
                        </p>
                    </div>

                    <div>
                        <div class="stars">{{ str_repeat('★', $item->rating) }}</div>
                        <strong>{{ $item->rating }}/5</strong>
                    </div>
                </div>

                <p><strong>Office:</strong> {{ $item->office->name ?? 'Office unavailable' }}</p>

                <p><strong>Your Comment:</strong></p>
                <p>{{ $item->comment ?: 'No comment provided.' }}</p>

                @if($item->office_reply)
                    <div class="reply-box">
                        <strong>Office Reply:</strong>
                        <p>{{ $item->office_reply }}</p>
                    </div>
                @else
                    <p class="muted">The office has not replied yet.</p>
                @endif

                <small class="muted">
                    Submitted on {{ $item->created_at->format('M d, Y h:i A') }}
                </small>
            </div>
        @empty
            <p>No feedback submitted yet.</p>
        @endforelse
    </div>
</main>
</body>
</html>