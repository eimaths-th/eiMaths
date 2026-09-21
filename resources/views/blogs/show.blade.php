@extends('layouts.app')

@section('style')
    @include('blogs.style')
@endsection

@section('content')
<div class="container p-3">
    <img class="col-12 col-md-6 p-0" src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ __('content-management.blog.form.thumbnail') }}" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
    <h1 class="my-4">{{ $blog->title }}</h1>
    <p><strong>{{ __('content-management.blog.show.author') }}</strong> {{ $blog->user->name ?? __('content-management.blog.show.unknown') }}</p>
    <p><strong>{{ __('content-management.blog.show.pen_name') }}</strong> {{ $blog->pen_name ?? __('content-management.blog.show.not_available') }}</p>
    <p><strong>{{ __('content-management.blog.show.published') }}</strong> {{ $blog->is_published ? __('content-management.blog.show.yes') : __('content-management.blog.show.no') }}</p>
    <p><strong>{{ __('content-management.blog.show.published_at') }}</strong> {{ $blog->published_at ? $blog->published_at->format('M d, Y H:i') : __('content-management.blog.show.not_available') }}</p>
    <hr>
    <div class="markdown-html-body">
        {!! $rendered_content !!}
    </div>
    <hr>
    <p><strong>{{ __('content-management.blog.show.meta_description') }}</strong> {{ $blog->meta_description }}</p>
    <p><strong>{{ __('content-management.blog.show.meta_keywords') }}</strong> {{ $blog->meta_keywords }}</p>
    <a href="{{ route('blogs.index') }}" class="btn btn-secondary">{{ __('content-management.blog.show.back') }}</a>
    <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-warning">{{ __('content-management.blog.show.edit') }}</a>
</div>
@endsection
