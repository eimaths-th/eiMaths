@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-content-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 id="affiliate-content-title" class="mb-0">{{ __('affiliate.content_admin.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-content-list-title">
                <div class="card-header">
                    <h2 id="affiliate-content-list-title" class="card-title">{{ __('affiliate.content_admin.title') }}</h2>
                </div>
                <div class="card-body p-0">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <caption class="sr-only">{{ __('affiliate.content_admin.title') }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('affiliate.content_admin.preview') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.media_title') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.type') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.campaign_code') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.destination') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.publication_window') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.status') }}</th>
                                    <th scope="col">{{ __('affiliate.content_admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contents as $index => $content)
                                    @php($is_archived = $content->trashed() || $content->archived_at !== null)
                                    <tr>
                                        <td>{{ ($contents->firstItem() ?? 1) + $index }}</td>
                                        <td>
                                            @if ($content->type === 'video')
                                                <video controls preload="metadata" width="120" height="80" src="{{ asset($content->file_path) }}" aria-label="{{ __('affiliate.media.video_preview', ['title' => $content->title]) }}"></video>
                                            @else
                                                <img src="{{ asset($content->file_path) }}" width="120" height="80" alt="{{ __('affiliate.media.image_preview', ['title' => $content->title]) }}">
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $content->title }}</strong>
                                            <p class="mb-0">{{ substr((string) $content->description, 0, 120) }}</p>
                                        </td>
                                        <td>{{ __('affiliate.content_admin.' . $content->type) }}</td>
                                        <td><code>{{ $content->campaign_code }}</code></td>
                                        <td><code>{{ $content->destination_target }}</code></td>
                                        <td>
                                            @if ($content->publish_starts_at)
                                                <time datetime="{{ $content->publish_starts_at->toISOString() }}">{{ $content->publish_starts_at->toDateTimeString() }}</time>
                                            @else
                                                —
                                            @endif
                                            –
                                            @if ($content->publish_ends_at)
                                                <time datetime="{{ $content->publish_ends_at->toISOString() }}">{{ $content->publish_ends_at->toDateTimeString() }}</time>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ __('affiliate.statuses.' . ($is_archived ? 'archived' : $content->status)) }}</td>
                                        <td>
                                            @if (! $is_archived)
                                                <div class="affiliate-table-actions">
                                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#affiliate-content-edit-{{ $content->id }}" aria-controls="affiliate-content-edit-{{ $content->id }}">
                                                        {{ __('affiliate.content_admin.edit') }}
                                                    </button>
                                                    <form method="POST" action="{{ route('affiliate-content-manage.toggle-status') }}">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $content->id }}">
                                                        <button class="btn btn-outline-secondary" type="submit">{{ $content->status === 'enabled' ? __('affiliate.content_admin.disable') : __('affiliate.content_admin.enable') }}</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('affiliate-content-manage.destroy', $content) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-outline-danger" type="submit" onclick="return confirm(@js(__('affiliate.content_admin.archive_confirmation')))" data-affiliate-archive-confirmation>{{ __('affiliate.content_admin.archive') }}</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span>{{ __('affiliate.statuses.archived') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9">{{ __('affiliate.content_admin.no_content') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($contents->hasPages())
                    <div class="card-footer">{{ $contents->withQueryString()->links() }}</div>
                @endif
            </section>

            @foreach ($contents as $content)
                @php($is_archived = $content->trashed() || $content->archived_at !== null)
                @if (! $is_archived)
                    <div class="modal fade" id="affiliate-content-edit-{{ $content->id }}" tabindex="-1" role="dialog" aria-labelledby="affiliate-content-edit-title-{{ $content->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title" id="affiliate-content-edit-title-{{ $content->id }}">{{ __('affiliate.content_admin.edit_title') }}</h2>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('affiliate.content_admin.cancel') }}"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <form method="POST" action="{{ route('affiliate-content-manage.update', $content) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="affiliate-form-grid">
                                            <div class="form-group">
                                                <label for="affiliate-content-title-{{ $content->id }}">{{ __('affiliate.content_admin.media_title') }}</label>
                                                <input class="form-control" id="affiliate-content-title-{{ $content->id }}" name="title" value="{{ $content->title }}" maxlength="255" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="affiliate-content-destination-{{ $content->id }}">{{ __('affiliate.content_admin.destination') }}</label>
                                                <input class="form-control" id="affiliate-content-destination-{{ $content->id }}" name="destination_target" value="{{ $content->destination_target }}" maxlength="2048" required>
                                            </div>
                                            <div class="form-group affiliate-form-field-full">
                                                <label for="affiliate-content-description-{{ $content->id }}">{{ __('affiliate.content_admin.description') }}</label>
                                                <textarea class="form-control" id="affiliate-content-description-{{ $content->id }}" name="description" maxlength="1000">{{ $content->description }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="affiliate-content-start-{{ $content->id }}">{{ __('affiliate.content_admin.publication_start') }}</label>
                                                <input class="form-control" id="affiliate-content-start-{{ $content->id }}" type="datetime-local" name="publish_starts_at" value="{{ optional($content->publish_starts_at)->format('Y-m-d\TH:i') }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="affiliate-content-end-{{ $content->id }}">{{ __('affiliate.content_admin.publication_end') }}</label>
                                                <input class="form-control" id="affiliate-content-end-{{ $content->id }}" type="datetime-local" name="publish_ends_at" value="{{ optional($content->publish_ends_at)->format('Y-m-d\TH:i') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <p class="text-muted small mr-auto mb-0">{{ __('affiliate.content_admin.immutable_campaign') }}</p>
                                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">{{ __('affiliate.content_admin.cancel') }}</button>
                                        <button class="btn btn-primary" type="submit">{{ __('affiliate.content_admin.update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <section id="affiliate-create-media" class="card card-primary affiliate-card" aria-labelledby="affiliate-create-media-title">
                <div class="card-header"><h2 id="affiliate-create-media-title" class="card-title">{{ __('affiliate.content_admin.create') }}</h2></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('affiliate-content-manage.store') }}" enctype="multipart/form-data" class="affiliate-form-grid">
                        @csrf
                        <div class="form-group">
                            <label for="affiliate-create-title">{{ __('affiliate.content_admin.media_title') }}</label>
                            <input class="form-control" id="affiliate-create-title" type="text" name="title" value="{{ old('title') }}" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label for="affiliate-create-type">{{ __('affiliate.content_admin.type') }}</label>
                            <select class="custom-select" id="affiliate-create-type" name="type" required>
                                <option value="image">{{ __('affiliate.content_admin.image') }}</option>
                                <option value="video">{{ __('affiliate.content_admin.video') }}</option>
                            </select>
                        </div>
                        <div class="form-group affiliate-form-field-full">
                            <label for="affiliate-create-file">{{ __('affiliate.content_admin.file') }}</label>
                            <input class="form-control-file" id="affiliate-create-file" type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv" required>
                            <small class="form-text text-muted">{{ __('affiliate.content_admin.file_help') }}</small>
                        </div>
                        <div class="form-group affiliate-form-field-full">
                            <label for="affiliate-create-description">{{ __('affiliate.content_admin.description') }}</label>
                            <textarea class="form-control" id="affiliate-create-description" name="description" maxlength="1000">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="affiliate-create-campaign">{{ __('affiliate.content_admin.campaign_code') }}</label>
                            <div class="input-group affiliate-campaign-code-input-group">
                                <input class="form-control" id="affiliate-create-campaign" type="text" name="campaign_code" value="{{ old('campaign_code') }}" maxlength="96" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" data-affiliate-campaign-code-input aria-describedby="affiliate-create-campaign-help affiliate-create-campaign-status" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" data-affiliate-campaign-code-generate data-affiliate-campaign-code-endpoint="{{ route('affiliate-content-manage.generate-campaign-code') }}" data-affiliate-campaign-code-generating-message="{{ __('affiliate.content_admin.campaign_code_generating') }}" data-affiliate-campaign-code-generated-message="{{ __('affiliate.content_admin.campaign_code_generated') }}" data-affiliate-campaign-code-generation-failed-message="{{ __('affiliate.content_admin.campaign_code_generation_failed') }}">{{ __('affiliate.content_admin.generate_campaign_code') }}</button>
                                </div>
                            </div>
                            <small id="affiliate-create-campaign-help" class="form-text text-muted">{{ __('affiliate.content_admin.campaign_code_help') }}</small>
                            <p id="affiliate-create-campaign-status" class="text-muted small mb-0" data-affiliate-campaign-code-status aria-live="polite"></p>
                        </div>
                        <div class="form-group">
                            <label for="affiliate-create-destination">{{ __('affiliate.content_admin.destination') }}</label>
                            <input class="form-control" id="affiliate-create-destination" type="text" name="destination_target" value="{{ old('destination_target', '/') }}" maxlength="2048" required>
                        </div>
                        <div class="form-group">
                            <label for="affiliate-create-start">{{ __('affiliate.content_admin.publication_start') }}</label>
                            <input class="form-control" id="affiliate-create-start" type="datetime-local" name="publish_starts_at" value="{{ old('publish_starts_at') }}">
                        </div>
                        <div class="form-group">
                            <label for="affiliate-create-end">{{ __('affiliate.content_admin.publication_end') }}</label>
                            <input class="form-control" id="affiliate-create-end" type="datetime-local" name="publish_ends_at" value="{{ old('publish_ends_at') }}">
                        </div>
                        <div class="affiliate-form-field-full">
                            <button class="btn btn-primary" type="submit">{{ __('affiliate.content_admin.upload') }}</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection

@section('script')
<script type="module" src="{{ asset('js/affiliate-campaign-code-generator.js') }}"></script>
@endsection
