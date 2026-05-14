<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Category</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Create Category</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.categories.index') }}" class="button secondary">Back</a>
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
            <form method="POST" action="{{ route('office.categories.store') }}">@csrf
                @if(isset($category)) @method('PUT') @endif
                    <div>
                        <label>Name</label>
                        <input name="name" value="{{ old('name', $category->name ?? '') }}">
                    </div>
                    <div>
                        <label>Description</label>
                        <textarea name="description">{{ old('description', $category->description ?? '') }}</textarea>
                    </div>
                    <div>
                        <button>Save Category</button>
                    </div>
                </form>
            </main>
        </body>
    </html>