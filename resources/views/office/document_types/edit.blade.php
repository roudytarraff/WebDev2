<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Document Type</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Edit Document Type</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>

            <div class="back-row">
                <a href="{{ route('office.document-types.index') }}" class="button secondary">Back</a>
            </div>

            @if($errors->any())
                <div class="alert error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('office.document-types.update', $documentType->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label>Name</label>
                    <input name="name" value="{{ old('name', $documentType->name) }}">
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="active" @selected(old('status', $documentType->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $documentType->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label>Description</label>
                    <textarea name="description">{{ old('description', $documentType->description) }}</textarea>
                </div>

                <div>
                    <button>Save Document Type</button>
                </div>
            </form>
        </main>
    </body>
</html>
