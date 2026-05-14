<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Track Request</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        <main class="tracking-page">
            <div class="auth-card tracking-card">

                <div class="auth-card-header">
                    <h2>{{ $serviceRequest->request_number }}</h2>
                    <p>Offline request tracking page</p>
                </div>

                <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? '' }}</p>
                <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? '' }}</p>
                <p><strong>Status:</strong> <span class="badge">{{ str_replace('_', ' ', $serviceRequest->status) }}</span></p>
                <p><strong>Submitted:</strong> {{ $serviceRequest->submitted_at }}</p>

                <h3>Status History</h3>
                @forelse($serviceRequest->statusHistory as $history)
                    <div class="message">
                        <strong>{{ str_replace('_', ' ', $history->new_status) }}</strong>
                        <br>
                        {{ $history->note }}
                        <br>
                        <small>{{ $history->changed_at }}</small>
                    </div>
                @empty
                    <p>No status history yet.</p>
                @endforelse
            </div>
        </main>
    </body>
</html>
