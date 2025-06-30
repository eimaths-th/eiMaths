@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Edit Blog</h1>
    <form action="{{ route('blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blog->title) }}" required>
            @error('title')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="slug">Slug (Optional, will be generated if empty. Example: my-awesome-blog-post)</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}">
            @error('slug')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail (Optional)</label>
            <input type="file" class="d-flex justify-content-center align-items-center @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/png, image/jpg, image/jpeg">
            @if($blog->thumbnail)
                <img class="mt-3" id="thumbnail_preview" src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Thumbnail Preview" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @else
                <img class="d-none mt-3" id="thumbnail_preview" alt="Thumbnail Preview" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @endif
            <div class="form-text text-danger">* Allowed file types: png, jpg, jpeg</div>
            <div class="form-text text-danger">* Max file size: 2MB</div>
            @error('thumbnail')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content', $blog->content) }}</textarea>
            @error('content')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <input type="text" class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" value="{{ old('meta_description', $blog->meta_description) }}">
            @error('meta_description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_keywords">Meta Keywords</label>
            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords) }}">
            @error('meta_keywords')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-check mb-3">
            <input type="hidden" name="is_published" value="0"> <!-- Hidden field for unchecked checkbox -->
            <input type="checkbox" class="form-check-input @error('is_published') is-invalid @enderror" id="is_published" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_published">Publish Now</label>
            @error('is_published')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Blog</button>
    </form>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const easymde = new EasyMDE({
            element: document.getElementById('content'),
            spellChecker: false,
            forceSync: true,
        });

        document.getElementById('thumbnail').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnail_preview').src = e.target.result;
                    document.getElementById('thumbnail_preview').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection
