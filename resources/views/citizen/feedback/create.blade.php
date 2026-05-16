<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Feedback</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .rating-options {
            display: grid;
            grid-template-columns: repeat(5, minmax(70px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .rating-option {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
        }

        .rating-option input {
            margin-bottom: 8px;
        }

        .stars {
            color: #f59e0b;
            font-size: 18px;
            letter-spacing: 1px;
        }

        textarea {
            min-height: 130px;
        }

        @media (max-width: 700px) {
            .rating-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Leave Feedback</h1>
            <p>{{ $serviceRequest->request_number }}</p>
        </div>
    </header>

    <div class="back-row">
        <a href="{{ route('citizen.requests.show', $serviceRequest->id) }}" class="button secondary">
            Back to Request
        </a>
    </div>

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="grid two">
        <div class="panel">
            <h2>Request Information</h2>

            <p><strong>Request Number:</strong> {{ $serviceRequest->request_number }}</p>
            <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'Service unavailable' }}</p>
            <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'Office unavailable' }}</p>
            <p><strong>Municipality:</strong> {{ $serviceRequest->office->municipality->name ?? 'Municipality unavailable' }}</p>
            <p><strong>Status:</strong> <span class="badge">{{ ucfirst($serviceRequest->status) }}</span></p>
        </div>

        <div class="panel">
            <h2>Feedback Rules</h2>

            <p>You can submit feedback only once for this completed request.</p>
            <p>Please rate your experience from 1 to 5 stars.</p>
        </div>
    </section>

    <div class="panel">
        <h2>Your Rating</h2>

        <form method="POST" action="{{ route('citizen.feedback.store', $serviceRequest->id) }}">
            @csrf

            <div>
                <label>Rating</label>

                <div class="rating-options">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="rating-option">
                            <input
                                type="radio"
                                name="rating"
                                value="{{ $i }}"
                                @checked((string) old('rating') === (string) $i)
                                required
                            >

                            <div class="stars">
                                {{ str_repeat('★', $i) }}
                            </div>

                            <strong>{{ $i }}/5</strong>
                        </label>
                    @endfor
                </div>
            </div>

            <div>
                <label>Comment</label>
                <textarea
                    name="comment"
                    placeholder="Write your feedback here..."
                >{{ old('comment') }}</textarea>
            </div>

            <button type="submit" class="button">
                Submit Feedback
            </button>
        </form>
    </div>
</main>
</body>
</html>