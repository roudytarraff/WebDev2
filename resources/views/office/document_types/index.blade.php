<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document Types</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Document Types</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <div class="panel">
                <div class="panel-head">
                    <h2>{{ $office->name }}</h2>
                    <a href="{{ route('office.document-types.create') }}" class="button">Create Document Type</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentTypes as $documentType)
                            <tr>
                                <td>{{ $documentType->name }}</td>
                                <td>{{ $documentType->description }}</td>
                                <td><span class="badge">{{ $documentType->status }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('office.document-types.edit', $documentType->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('office.document-types.destroy', $documentType->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No document types yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>
