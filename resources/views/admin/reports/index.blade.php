<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reports</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('admin.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Reports</h1>
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
            <section class="stats-grid">
                <div class="stat">
                    <span>Total Revenue</span>
                    <strong>${{ number_format($totalRevenue, 2) }}</strong>
                </div>
                <div class="stat">
                    <span>Pending Revenue</span>
                    <strong>${{ number_format($pendingRevenue, 2) }}</strong>
                </div>
            </section>
            <section class="grid two">
                <div class="panel">
                    <h2>Requests Per Office</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Municipality</th>
                                <th>Requests</th>
                            </tr>
                        </thead>
                        <tbody>@foreach($offices as $office)<tr>
                            <td>{{ $office->name }}</td>
                            <td>{{ $office->municipality->name ?? '' }}</td>
                            <td>{{ $office->service_requests_count }}</td>
                        </tr>@endforeach</tbody>
                    </table>
                </div>
                <div class="panel">
                    <h2>Revenue Per Office</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Municipality</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>@foreach($revenueByOffice as $office)<tr>
                            <td>{{ $office->name }}</td>
                            <td>{{ $office->municipality->name ?? '' }}</td>
                            <td>${{ number_format($office->revenue, 2) }}</td>
                        </tr>@endforeach</tbody>
                    </table>
                </div>
            </section>
            <div class="panel">
                <h2>Requests By Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>@foreach($requestsByStatus as $row)<tr>
                        <td>
                            <span class="badge">{{ str_replace('_', ' ', $row->status) }}</span>
                        </td>
                        <td>{{ $row->total }}</td>
                    </tr>@endforeach</tbody>
                </table>
            </div>
        </main>
    </body>
</html>