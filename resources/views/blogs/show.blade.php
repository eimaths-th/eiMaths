@extends('layouts.app')

@section('content')
<div class="container p-3">
    <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Thumbnail" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
    <h1>{{ $blog->title }}</h1>
    <p><strong>Published:</strong> {{ $blog->is_published ? 'Yes' : 'No' }}</p>
    <p><strong>Published At:</strong> {{ $blog->published_at ? $blog->published_at->format('M d, Y H:i') : 'N/A' }}</p>
    <hr>
    <div>
        <div>{!! Str::markdown($blog->content) !!}</div>
    </div>
    <hr>
    <p><strong>Meta Description:</strong> {{ $blog->meta_description }}</p>
    <p><strong>Meta Keywords:</strong> {{ $blog->meta_keywords }}</p>
    <a href="{{ route('blogs.index') }}" class="btn btn-secondary">Back to Blog</a>
    <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-warning">Edit Blog</a>
</div>
@endsection
