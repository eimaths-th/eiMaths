@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-media-title">
    @include('affiliate.partials.navigation')
    <h1 id="affiliate-media-title">Affiliate media library</h1>
    <form method="GET" action="{{ route('affiliate.media.index') }}">
        <label for="affiliate-media-search">Search</label>
        <input id="affiliate-media-search" name="search" maxlength="100" value="{{ $search ?? '' }}">
        <label for="affiliate-media-type">Type</label>
        <select id="affiliate-media-type" name="type">
            <option value="">All</option>
            <option value="image" @selected(($type ?? '') === 'image')>Image</option>
            <option value="video" @selected(($type ?? '') === 'video')>Video</option>
        </select>
        <button type="submit">Filter</button>
    </form>
    <p>{{ $media->total() }} media assets</p>
    @if ($media->isEmpty())
        <p>No media matches these filters. Clear the search and type filter to see approved media.</p>
    @else
        <div class="affiliate-media-grid">
            @foreach ($media as $content)
                <article class="affiliate-media-card" data-media-card>
                    <h2>{{ $content->title }}</h2>
                    @if ($content->type === 'video')
                        <video controls preload="metadata" src="{{ asset($content->file_path) }}" aria-label="{{ $content->title }}"></video>
                    @else
                        <img src="{{ asset($content->file_path) }}" alt="{{ $content->title }}">
                    @endif
                    <p>{{ $content->description }}</p>
                    <p>Campaign: <code>{{ $content->campaign_code }}</code></p>
                    <a href="{{ asset($content->file_path) }}" download>Download asset</a>
                    <button type="button" data-media-copy-caption="{{ $content->description }}">Copy caption</button>
                    <button type="button" data-media-copy-link="{{ $content->tracking_url }}">Copy tracking link</button>
                    <button type="button" data-media-share data-media-title="{{ $content->title }}" data-media-caption="{{ $content->description }}" data-media-url="{{ $content->tracking_url }}">Share</button>
                    <p role="status" aria-live="polite" data-media-feedback></p>
                </article>
            @endforeach
        </div>
        {{ $media->withQueryString()->links() }}
    @endif
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection

@section('script')
<script type="module" src="{{ asset('js/affiliate-media-library.js') }}"></script>
<script type="module">import { InitializeAffiliateMediaLibrary } from '{{ asset('js/affiliate-media-library.js') }}'; InitializeAffiliateMediaLibrary();</script>
@endsection
