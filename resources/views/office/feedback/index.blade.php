<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .stars {
            color: #f59e0b;
            letter-spacing: 1px;
        }

        .comment-preview {
            max-width: 280px;
            white-space: normal;
            line-height: 1.4;
        }
    </style>
</head>

<body>
@include('office.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Feedback</h1>
            <p>{{ Auth::user()->full_name ?? '' }}</p>
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

    <section class="stats-grid">
        <div class="stat">
            <span>Total Feedback</span>
            <strong>{{ $totalFeedback }}</strong>
        </div>

        <div class="stat">
            <span>Average Rating</span>
            <strong>{{ number_format((float) $averageRating, 1) }}/5</strong>
        </div>

        <div class="stat">
            <span>5-Star Reviews</span>
            <strong>{{ $fiveStarCount }}</strong>
        </div>

        <div class="stat">
            <span>Pending Replies</span>
            <strong>{{ $pendingReplies }}</strong>
        </div>
    </section>

    <section class="grid two">
        <div class="panel">
            <h2>Rating Distribution</h2>

            @foreach($ratingCounts as $rating => $count)
                <p>
                    <span class="stars">{{ str_repeat('★', $rating) }}</span>
                    <strong>{{ $count }}</strong>
                    feedback
                </p>
            @endforeach
        </div>

        <div class="panel">
            <h2>Office</h2>
            <p><strong>Office:</strong> {{ $office->name }}</p>
            <p><strong>Status:</strong> {{ ucfirst($office->status) }}</p>
        </div>
    </section>

    <div class="panel">
        <div class="panel-head">
            <h2>Feedback Records</h2>
            <span class="badge">{{ $feedback->count() }} records</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Citizen</th>
                    <th>Request</th>
                    <th>Service</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Reply</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($feedback as $item)
                    <tr>
                        <td>{{ $item->citizen->full_name ?? 'Citizen unavailable' }}</td>

                        <td>{{ $item->request->request_number ?? 'No request' }}</td>

                        <td>{{ $item->request->service->name ?? 'Service unavailable' }}</td>

                        <td>
                            <span class="stars">{{ str_repeat('★', $item->rating) }}</span>
                            <br>
                            {{ $item->rating }}/5
                        </td>

                        <td>
                            <div class="comment-preview">
                                {{ $item->comment ?: 'No comment provided.' }}
                            </div>
                        </td>

                        <td>
                            @if($item->office_reply)
                                <span class="badge">Replied</span>
                            @else
                                <span class="badge">Pending</span>
                            @endif
                        </td>

                        <td>{{ $item->created_at->format('M d, Y') }}</td>

                        <td>
                            <a href="{{ route('office.feedback.show', $item->id) }}">
                                View / Reply
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No feedback yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
</body>
</html>