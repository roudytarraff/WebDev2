<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Service</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Create Service</h1>
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
            <form method="POST" action="{{ route('office.services.store') }}">@csrf
                @if(isset($service)) @method('PUT') @endif
                    <div>
                        <label>Category</label>
                        <select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $service->category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label>Name</label>
                        <input name="name" value="{{ old('name', $service->name ?? '') }}">
                    </div>
                    <div>
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $service->price ?? '0') }}">
                    </div>
                    <div>
                        <label>Duration Minutes</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes ?? '30') }}">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">@foreach(['active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $service->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label>Requires Appointment</label>
                        <select name="requires_appointment">
                            <option value="0" @selected(!old('requires_appointment', $service->requires_appointment ?? false))>No</option>
                            <option value="1" @selected(old('requires_appointment', $service->requires_appointment ?? false))>Yes</option>
                        </select>
                    </div>
                    <div>
                        <label>Online Card Payment</label>
                        <select name="supports_online_payment">
                            <option value="0" @selected(!old('supports_online_payment', $service->supports_online_payment ?? false))>No</option>
                            <option value="1" @selected(old('supports_online_payment', $service->supports_online_payment ?? false))>Yes</option>
                        </select>
                    </div>
                    <div>
                        <label>Crypto Payment</label>
                        <select name="supports_crypto_payment">
                            <option value="0" @selected(!old('supports_crypto_payment', $service->supports_crypto_payment ?? false))>No</option>
                            <option value="1" @selected(old('supports_crypto_payment', $service->supports_crypto_payment ?? false))>Yes</option>
                        </select>
                    </div>
                    <div>
                        <label>Description</label>
                        <textarea name="description">{{ old('description', $service->description ?? '') }}</textarea>
                    </div>
                    <div>
                        <label>Instructions</label>
                        <textarea name="instructions">{{ old('instructions', $service->instructions ?? '') }}</textarea>
                    </div>
                    <div>
                        <label>Required Documents</label>
                        <select name="document_type_ids[]" multiple>
                            @foreach($documentTypes as $documentType)
                                <option value="{{ $documentType->id }}" @selected(collect(old('document_type_ids', []))->contains($documentType->id))>{{ $documentType->name }}</option>
                            @endforeach
                        </select>
                        <p class="muted">Hold Ctrl to choose multiple document types. Add new options from Document Types.</p>
                    </div>
                    <div>
                        <button>Save Service</button>
                    </div>
                </form>
            </main>
        </body>
    </html>