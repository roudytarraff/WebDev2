<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feedback</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
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
                <table>
                    <thead>
                        <tr>
                            <th>Citizen</th>
                            <th>Request</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Reply</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($feedback as $item)<tr>
                        <td>{{ $item->citizen->full_name ?? '' }}</td>
                        <td>{{ $item->request->request_number ?? '' }}</td>
                        <td>{{ $item->rating }}/5</td>
                        <td>{{ $item->comment }}</td>
                        <td>{{ $item->office_reply ? 'Replied' : 'No reply' }}</td>
                        <td>
                            <a href="{{ route('office.feedback.show', $item->id) }}">Reply</a>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="6">No feedback yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>