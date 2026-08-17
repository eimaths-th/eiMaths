@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-media-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-media-title" class="mb-0">{{ __('affiliate.media.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-media-filter-title">
                <div class="card-header">
                    <h2 id="affiliate-media-filter-title" class="card-title">{{ __('affiliate.media.filter') }}</h2>
                </div>
                <div class="card-body">
                    <form class="affiliate-filter-form" method="GET" action="{{ route('affiliate.media.index') }}">
                        <div class="form-group flex-grow-1">
                            <label for="affiliate-media-search">{{ __('affiliate.media.search') }}</label>
                            <input class="form-control" id="affiliate-media-search" name="search" maxlength="100" value="{{ $search ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="affiliate-media-type">{{ __('affiliate.media.type') }}</label>
                            <select class="custom-select" id="affiliate-media-type" name="type">
                                <option value="">{{ __('affiliate.media.all') }}</option>
                                <option value="image" @selected(($type ?? '') === 'image')>{{ __('affiliate.media.image') }}</option>
                                <option value="video" @selected(($type ?? '') === 'video')>{{ __('affiliate.media.video') }}</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">{{ __('affiliate.media.filter') }}</button>
                    </form>
                </div>
            </section>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <p class="text-muted mb-0">{{ __('affiliate.media.result_count', ['count' => $media->total()]) }}</p>
            </div>

            @if ($media->isEmpty())
                <section class="card card-primary affiliate-card" aria-live="polite">
                    <div class="card-body">{{ __('affiliate.media.no_results') }}</div>
                </section>
            @else
                <div class="affiliate-media-grid">
                    @foreach ($media as $content)
                        <article class="card affiliate-media-card" data-media-card>
                            <div class="card-header">
                                <h2 class="card-title mb-0">{{ $content->title }}</h2>
                            </div>
                            <div class="card-body">
                                @if ($content->type === 'video')
                                    <video class="affiliate-media-preview mb-3" controls preload="metadata" src="{{ asset($content->file_path) }}" aria-label="{{ __('affiliate.media.video_preview', ['title' => $content->title]) }}"></video>
                                @else
                                    <img class="affiliate-media-preview mb-3" src="{{ asset($content->file_path) }}" alt="{{ __('affiliate.media.image_preview', ['title' => $content->title]) }}">
                                @endif
                                <p>{{ $content->description }}</p>
                                <p class="mb-2"><strong>{{ __('affiliate.media.campaign') }}:</strong> <code>{{ $content->campaign_code }}</code></p>
                                <div class="form-group mb-3">
                                    <label for="affiliate-media-tracking-url-{{ $content->id }}">{{ __('affiliate.media.tracking_link_label') }}</label>
                                    <input
                                        class="form-control"
                                        id="affiliate-media-tracking-url-{{ $content->id }}"
                                        type="text"
                                        value="{{ $content->tracking_url }}"
                                        readonly
                                    >
                                </div>
                                <p class="affiliate-confirmation mb-0">{{ __('affiliate.media.publication_status') }}</p>
                            </div>
                            <div class="card-footer">
                                <div class="affiliate-media-actions">
                                    <a class="btn btn-primary" href="{{ asset($content->file_path) }}" download>{{ __('affiliate.media.download') }}</a>
                                    <button class="btn btn-outline-secondary" type="button" data-media-share data-media-title="{{ $content->title }}" data-media-caption="{{ $content->description }}" data-media-url="{{ $content->tracking_url }}">{{ __('affiliate.media.share') }}</button>
                                </div>
                                <p class="affiliate-action-feedback mb-0 mt-2" role="status" aria-live="polite" data-media-feedback></p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-4">{{ $media->withQueryString()->links() }}</div>
            @endif

            @php
                $media_translations = [
                    'copied' => __('affiliate.media.copied'),
                    'shared' => __('affiliate.media.shared'),
                    'tracking_link_copied' => __('affiliate.media.tracking_link_copied'),
                    'action_unavailable' => __('affiliate.media.action_unavailable'),
                ];
            @endphp
            <script id="affiliate-media-translations" type="application/json" data-media-translations>@json($media_translations)</script>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection

@section('script')
<script type="module" src="{{ asset('js/affiliate-media-library.js') }}"></script>
<script type="module">import { InitializeAffiliateMediaLibrary } from '{{ asset('js/affiliate-media-library.js') }}'; InitializeAffiliateMediaLibrary();</script>
@endsection
