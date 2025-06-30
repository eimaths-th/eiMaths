@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Blogs</h1>
    <a href="{{ route('blogs.create') }}" class="btn btn-primary mb-3">Create New Blog</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($blogs as $blog)
                <tr>
                    <td><img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Thumbnail" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;"></td>
                    <td>{{ $blog->title }}</td>
                    <td>{{ $blog->is_published ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('blogs.show', $blog) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('blogs.destroy', $blog) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $blogs->links() }}
</div>
@endsection
