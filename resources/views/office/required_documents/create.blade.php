<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Required Document</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Add Required Document</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>

            <div class="back-row">
                <a href="{{ route('office.services.show', $service->id) }}" class="button secondary">Back</a>
            </div>

            @if($errors->any())
                <div class="alert error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('office.required-documents.store', $service->id) }}">
                @csrf

                <div>
                    <label>Document Type</label>
                    <select name="document_type_id">
                        <option value="">Choose document type</option>
                        @foreach($documentTypes as $documentType)
                            <option value="{{ $documentType->id }}" @selected(old('document_type_id') == $documentType->id)>{{ $documentType->name }}</option>
                        @endforeach
                    </select>
                    <p class="muted">Create new document types from the Document Types page.</p>
                </div>

                <div>
                    <label>Required</label>
                    <select name="is_required">
                        <option value="1" @selected(old('is_required', true))>Yes</option>
                        <option value="0" @selected(!old('is_required', true))>No</option>
                    </select>
                </div>

                <div>
                    <button>Save Document</button>
                </div>
            </form>
        </main>
    </body>
</html>
