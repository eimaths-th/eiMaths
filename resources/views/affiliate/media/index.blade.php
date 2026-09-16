@extends('layouts.app')

@section('content')
<main class="affiliate-portal affiliate-media-page" aria-labelledby="affiliate-media-title">
    <section class="content-header affiliate-media-heading">
        <div class="container-fluid">
            <div class="affiliate-media-heading__content">
                <div>
                    <p class="affiliate-media-heading__eyebrow mb-1">{{ __('affiliate.media.eyebrow') }}</p>
                    <h1 id="affiliate-media-title" class="mb-1">{{ __('affiliate.media.title') }}</h1>
                    <p class="affiliate-media-heading__subtitle mb-0">{{ __('affiliate.media.subtitle') }}</p>
                </div>
                <div class="affiliate-media-heading__total" aria-label="{{ __('affiliate.media.result_count', ['count' => $media->total()]) }}">
                    <strong>{{ number_format($media->total()) }}</strong>
                    <span>{{ __('affiliate.media.items') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="content affiliate-media-content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

            @php
                $current_type = $type ?? '';
                $folder_query = ($search ?? '') !== '' ? ['search' => $search] : [];
                $folder_types = [
                    '' => [
                        'icon' => 'fa-folder-open',
                        'label' => __('affiliate.media.folder_all'),
                        'description' => __('affiliate.media.folder_all_help'),
                    ],
                    'image' => [
                        'icon' => 'fa-images',
                        'label' => __('affiliate.media.folder_images'),
                        'description' => __('affiliate.media.folder_images_help'),
                    ],
                    'video' => [
                        'icon' => 'fa-video',
                        'label' => __('affiliate.media.folder_videos'),
                        'description' => __('affiliate.media.folder_videos_help'),
                    ],
                ];
            @endphp

            <section class="affiliate-media-folders" aria-labelledby="affiliate-media-folders-title">
                <div class="affiliate-media-section-heading">
                    <div>
                        <h2 id="affiliate-media-folders-title">{{ __('affiliate.media.folders_title') }}</h2>
                        <p>{{ __('affiliate.media.folders_help') }}</p>
                    </div>
                </div>
                <div class="affiliate-media-folder-grid" data-tutorial-folders>
                    @foreach ($folder_types as $folder_type => $folder)
                        @php
                            $folder_params = $folder_query;
                            if ($folder_type !== '') $folder_params['type'] = $folder_type;
                            $folder_is_active = $current_type === $folder_type;
                        @endphp
                        <a
                            class="affiliate-media-folder{{ $folder_is_active ? ' is-active' : '' }}"
                            href="{{ route('affiliate.media.index', $folder_params) }}"
                            @if ($folder_is_active) aria-current="page" @endif
                        >
                            <span class="affiliate-media-folder__icon" aria-hidden="true"><i class="fas {{ $folder['icon'] }}"></i></span>
                            <span class="affiliate-media-folder__copy">
                                <strong>{{ $folder['label'] }}</strong>
                                <small>{{ $folder['description'] }}</small>
                            </span>
                            <i class="fas fa-chevron-right affiliate-media-folder__arrow" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="affiliate-media-toolbar" data-tutorial-search aria-labelledby="affiliate-media-filter-title">
                <div class="affiliate-media-toolbar__heading">
                    <h2 id="affiliate-media-filter-title">{{ __('affiliate.media.search_library') }}</h2>
                    <p>{{ __('affiliate.media.search_help') }}</p>
                </div>
                <form class="affiliate-media-toolbar__form" method="GET" action="{{ route('affiliate.media.index') }}">
                    @if ($current_type !== '')
                        <input type="hidden" name="type" value="{{ $current_type }}">
                    @endif
                    <label class="sr-only" for="affiliate-media-search">{{ __('affiliate.media.search') }}</label>
                    <div class="affiliate-media-search-control">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input
                            class="form-control"
                            id="affiliate-media-search"
                            name="search"
                            maxlength="100"
                            value="{{ $search ?? '' }}"
                            placeholder="{{ __('affiliate.media.search_placeholder') }}"
                        >
                    </div>
                    <button class="btn btn-primary affiliate-media-search-button" type="submit">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <span>{{ __('affiliate.media.search') }}</span>
                    </button>
                    @if (($search ?? '') !== '' || $current_type !== '')
                        <a class="btn btn-link affiliate-media-clear" href="{{ route('affiliate.media.index') }}">{{ __('affiliate.media.clear_filters') }}</a>
                    @endif
                </form>
            </section>

            <div class="affiliate-media-section-heading affiliate-media-results-heading">
                <div>
                    <h2>{{ __('affiliate.media.library_title') }}</h2>
                    <p>{{ __('affiliate.media.current_folder', ['folder' => $folder_types[$current_type]['label'] ?? __('affiliate.media.folder_all')]) }}</p>
                </div>
                <span class="affiliate-media-result-pill">{{ __('affiliate.media.result_count', ['count' => $media->total()]) }}</span>
            </div>

            @if ($media->isEmpty())
                <section class="affiliate-media-empty" aria-live="polite">
                    <span class="affiliate-media-empty__icon" aria-hidden="true"><i class="far fa-folder-open"></i></span>
                    <h2>{{ __('affiliate.media.no_results_title') }}</h2>
                    <p>{{ __('affiliate.media.no_results') }}</p>
                    <a class="btn btn-primary" href="{{ route('affiliate.media.index') }}">{{ __('affiliate.media.view_all') }}</a>
                </section>
            @else
                <div class="affiliate-media-grid">
                    @foreach ($media as $content)
                        @php
                            $asset_url = asset($content->file_path);
                            $file_extension = strtoupper(pathinfo($content->file_path, PATHINFO_EXTENSION));
                            $is_video = $content->type === 'video';
                            $menu_id = 'affiliate-media-menu-' . $content->id;
                        @endphp
                        <article
                            class="affiliate-media-card"
                            data-media-card
                            data-media-title="{{ $content->title }}"
                            data-media-caption="{{ $content->description }}"
                            data-media-url="{{ $content->tracking_url }}"
                            data-media-download-url="{{ $asset_url }}"
                            data-media-type="{{ $content->type }}"
                        >
                            <div class="affiliate-media-card__preview">
                                <span class="affiliate-media-type-badge">
                                    <i class="fas {{ $is_video ? 'fa-video' : 'fa-image' }}" aria-hidden="true"></i>
                                    {{ $is_video ? __('affiliate.media.video') : __('affiliate.media.image') }}
                                </span>
                                <div class="affiliate-media-card__menu-wrap" data-media-menu-wrap>
                                    <button
                                        class="affiliate-media-menu-toggle"
                                        type="button"
                                        data-media-menu-toggle
                                        aria-expanded="false"
                                        aria-controls="{{ $menu_id }}"
                                        aria-label="{{ __('affiliate.media.open_actions', ['title' => $content->title]) }}"
                                    >
                                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                    </button>
                                    <div class="affiliate-media-menu" id="{{ $menu_id }}" data-media-menu hidden>
                                        <a href="{{ $asset_url }}" download>
                                            <i class="fas fa-download" aria-hidden="true"></i>
                                            {{ __('affiliate.media.download') }}
                                        </a>
                                        <button type="button" data-media-share-panel>
                                            <i class="fas fa-share-alt" aria-hidden="true"></i>
                                            {{ __('affiliate.media.share') }}
                                        </button>
                                    </div>
                                </div>

                                <a
                                    class="affiliate-media-preview-link"
                                    href="{{ $asset_url }}"
                                    target="_blank"
                                    rel="noopener"
                                    data-media-view
                                    data-media-view-url="{{ $asset_url }}"
                                    data-media-view-title="{{ $content->title }}"
                                    data-media-view-type="{{ $content->type }}"
                                    aria-haspopup="dialog"
                                    aria-label="{{ __('affiliate.media.view_full', ['title' => $content->title]) }}"
                                >
                                    @if ($is_video)
                                        <video class="affiliate-media-preview" preload="metadata" muted playsinline src="{{ $asset_url }}" aria-label="{{ __('affiliate.media.video_preview', ['title' => $content->title]) }}"></video>
                                        <span class="affiliate-media-play" aria-hidden="true"><i class="fas fa-play"></i></span>
                                    @else
                                        <img class="affiliate-media-preview" src="{{ $asset_url }}" loading="lazy" alt="{{ __('affiliate.media.image_preview', ['title' => $content->title]) }}">
                                    @endif
                                </a>
                            </div>

                            <div class="affiliate-media-card__body">
                                <div class="affiliate-media-card__meta">
                                    <span>{{ $file_extension ?: ($is_video ? 'VIDEO' : 'IMAGE') }}</span>
                                    <span class="affiliate-media-ready"><i class="fas fa-check-circle" aria-hidden="true"></i> {{ __('affiliate.media.ready_to_use') }}</span>
                                </div>
                                <h2>{{ $content->title }}</h2>
                                @if ($content->description)
                                    <p class="affiliate-media-card__description">{{ $content->description }}</p>
                                @endif
                            </div>

                            <div class="affiliate-media-card__footer">
                                <a class="btn btn-primary" href="{{ $asset_url }}" download>
                                    <i class="fas fa-download" aria-hidden="true"></i>
                                    {{ __('affiliate.media.download_short') }}
                                </a>
                                <button class="btn btn-light" type="button" data-media-share-panel>
                                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                                    {{ __('affiliate.media.share') }}
                                </button>
                            </div>
                            <p class="affiliate-action-feedback" role="status" aria-live="polite" data-media-feedback></p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-4">{{ $media->withQueryString()->links() }}</div>
            @endif

            <dialog class="affiliate-media-dialog" data-media-dialog aria-labelledby="affiliate-media-dialog-title">
                <div class="affiliate-media-dialog__header">
                    <h2 class="affiliate-media-dialog__title" id="affiliate-media-dialog-title" data-media-dialog-title></h2>
                    <button class="affiliate-media-dialog__close btn btn-light" type="button" data-media-dialog-close aria-label="{{ __('affiliate.media.close_dialog') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="affiliate-media-dialog__content" data-media-dialog-content></div>
            </dialog>

            <dialog class="affiliate-media-share-drawer" data-media-share-drawer aria-labelledby="affiliate-media-share-title">
                <div class="affiliate-media-share-drawer__header">
                    <div>
                        <p class="affiliate-media-share-drawer__eyebrow">{{ __('affiliate.media.share_panel_eyebrow') }}</p>
                        <h2 id="affiliate-media-share-title">{{ __('affiliate.media.share_panel_title') }}</h2>
                    </div>
                    <button class="affiliate-media-share-drawer__close" type="button" data-media-share-close aria-label="{{ __('affiliate.media.close_share_panel') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="affiliate-media-share-drawer__body">
                    <div class="affiliate-media-share-summary">
                        <span class="affiliate-media-share-summary__icon" data-media-share-icon aria-hidden="true"><i class="fas fa-photo-video"></i></span>
                        <div>
                            <h3 data-media-share-title></h3>
                            <p data-media-share-caption></p>
                        </div>
                    </div>

                    <section class="affiliate-media-share-section" aria-labelledby="affiliate-media-share-destination-title">
                        <h3 id="affiliate-media-share-destination-title">{{ __('affiliate.media.share_to') }}</h3>
                        <div class="affiliate-media-share-destinations">
                            <a class="affiliate-media-share-destination is-facebook" data-media-share-facebook href="#" target="_blank" rel="noopener">
                                <i class="fab fa-facebook-f" aria-hidden="true"></i>
                                <span>{{ __('affiliate.media.share_facebook') }}</span>
                            </a>
                            <a class="affiliate-media-share-destination is-line" data-media-share-line href="#" target="_blank" rel="noopener">
                                <i class="fab fa-line" aria-hidden="true"></i>
                                <span>{{ __('affiliate.media.share_line') }}</span>
                            </a>
                            <button class="affiliate-media-share-destination is-device" type="button" data-media-share>
                                <i class="fas fa-share-alt" aria-hidden="true"></i>
                                <span>{{ __('affiliate.media.share_device') }}</span>
                            </button>
                        </div>
                    </section>

                    <p class="affiliate-action-feedback" role="status" aria-live="polite" data-media-feedback></p>
                </div>
                <div class="affiliate-media-share-drawer__footer">
                    <a class="btn btn-primary btn-block" href="#" download data-media-share-download>
                        <i class="fas fa-download" aria-hidden="true"></i>
                        {{ __('affiliate.media.download') }}
                    </a>
                </div>
            </dialog>

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
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}?v={{ filemtime(public_path('css/affiliate-portal.css')) }}">
@endsection

@section('script')
<script type="module">
    import {
        InitializeAffiliateMediaLibrary,
        InitializeAffiliateMediaMenus,
        InitializeAffiliateMediaShareDrawer,
        InitializeAffiliateMediaViewer,
    } from '{{ asset('js/affiliate-media-library.js') }}';

    document.body.classList.remove('layout-footer-fixed');
    document.body.classList.add('affiliate-media-layout');

    InitializeAffiliateMediaLibrary();
    InitializeAffiliateMediaMenus();
    InitializeAffiliateMediaShareDrawer();
    InitializeAffiliateMediaViewer();
</script>
@endsection
