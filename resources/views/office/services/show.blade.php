<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Service Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.services.index') }}" class="button secondary">Back</a>
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
                    <h2>Details</h2>
                    <p>
                        <strong>Category:</strong> {{ $service->category->name ?? '' }}</p>
                        <p>
                            <strong>Price:</strong> ${{ number_format($service->price, 2) }}</p>
                            <p>
                                <strong>Duration:</strong> {{ $service->duration_minutes }} minutes</p>
                                <p>
                                    <strong>Status:</strong> <span class="badge">{{ $service->status }}</span>
                                </p>
                                <p>{{ $service->description }}</p>
                            </div>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Required Documents</h2>
                                    <a class="button" href="{{ route('office.required-documents.create', $service->id) }}">Add Document</a>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Required</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>@forelse($service->documents as $document)<tr>
                                        <td>{{ $document->document_name }}</td>
                                        <td>{{ $document->is_required ? 'Yes' : 'No' }}</td>
                                        <td class="actions">
                                            <a href="{{ route('office.required-documents.edit', $document->id) }}">Edit</a>
                                            <form method="POST" action="{{ route('office.required-documents.destroy', $document->id) }}">@csrf
                                                @method('DELETE')<button class="danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>@empty<tr>
                                    <td colspan="3">No documents defined.</td>
                                </tr>@endforelse</tbody>
                            </table>
                        </div>
                    </section>
                </main>
            </body>
        </html>