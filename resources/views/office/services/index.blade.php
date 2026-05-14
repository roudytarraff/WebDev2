<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Services</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Services</h1>
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
                <div class="panel-head">
                    <h2>{{ $office->name }} Services</h2>
                    <a class="button" href="{{ route('office.services.create') }}">Create Service</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Required Docs</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($services as $service)<tr>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->category->name ?? '' }}</td>
                        <td>${{ number_format($service->price, 2) }}</td>
                        <td>
                            <a href="{{ route('office.services.show', $service->id) }}">{{ $service->documents->count() }} documents</a>
                        </td>
                        <td>{{ $service->supports_online_payment ? 'Card ' : '' }}{{ $service->supports_crypto_payment ? 'Crypto' : '' }}</td>
                        <td>
                            <span class="badge">{{ $service->status }}</span>
                        </td>
                        <td class="actions">
                            <a href="{{ route('office.services.show', $service->id) }}">Manage Docs</a>
                            <a href="{{ route('office.services.edit', $service->id) }}">Edit</a>
                            <form method="POST" action="{{ route('office.services.destroy', $service->id) }}">@csrf
                                @method('DELETE')<button class="danger">Delete</button>
                            </form>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="7">No services yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>