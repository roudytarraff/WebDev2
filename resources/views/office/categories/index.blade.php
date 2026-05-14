<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Categories</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Service Categories</h1>
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
                    <h2>Categories</h2>
                    <a class="button" href="{{ route('office.categories.create') }}">Create Category</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Services</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($categories as $category)<tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description }}</td>
                        <td>{{ $category->services_count }}</td>
                        <td class="actions">
                            <a href="{{ route('office.categories.edit', $category->id) }}">Edit</a>
                            <form method="POST" action="{{ route('office.categories.destroy', $category->id) }}">@csrf
                                @method('DELETE')<button class="danger">Delete</button>
                            </form>
                        </td>
                    </tr>@empty<tr>
                    <td colspan="4">No categories yet.</td>
                </tr>@endforelse</tbody>
            </table>
        </div>
    </main>
</body>
</html>