<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Reply</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .stars {
            color: #f59e0b;
            font-size: 20px;
            letter-spacing: 2px;
        }

        .feedback-comment {
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        textarea {
            min-height: 140px;
        }
    </style>
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
        <a href="{{ route('office.feedback.index') }}" class="button secondary">
            Back to Feedback
        </a>
    </div>

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

    <section class="grid two">
        <div class="panel">
            <h2>Feedback Details</h2>

            <p><strong>Citizen:</strong> {{ $feedback->citizen->full_name ?? 'Citizen unavailable' }}</p>
            <p><strong>Request:</strong> {{ $feedback->request->request_number ?? 'No request' }}</p>
            <p><strong>Service:</strong> {{ $feedback->request->service->name ?? 'Service unavailable' }}</p>
            <p><strong>Office:</strong> {{ $feedback->office->name ?? 'Office unavailable' }}</p>

            <p>
                <strong>Submitted:</strong>
                {{ $feedback->created_at->format('M d, Y h:i A') }}
            </p>

            <p><strong>Rating:</strong></p>
            <div class="stars">
                {{ str_repeat('★', $feedback->rating) }}
            </div>
            <p>{{ $feedback->rating }}/5</p>

            <div class="feedback-comment">
                <strong>Citizen Comment:</strong>
                <p>{{ $feedback->comment ?: 'No comment provided.' }}</p>
            </div>
        </div>

        <div class="panel">
            <h2>Office Reply</h2>

            <form method="POST" action="{{ route('office.feedback.reply', $feedback->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label>Reply</label>
                    <textarea
                        name="office_reply"
                        placeholder="Write your reply to the citizen..."
                        required
                    >{{ old('office_reply', $feedback->office_reply) }}</textarea>
                </div>

                <button type="submit" class="button">
                    Save Reply
                </button>
            </form>

            @if($feedback->office_reply)
                <div class="feedback-comment">
                    <strong>Current Reply:</strong>
                    <p>{{ $feedback->office_reply }}</p>
                </div>
            @endif
        </div>
    </section>
</main>
</body>
</html>