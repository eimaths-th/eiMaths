@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('crm.tracking_links.title') }}</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('tracking-links.report') }}" class="btn btn-info float-sm-right">
                    <i class="fas fa-chart-bar mr-1"></i> {{ __('crm.tracking_links.view_report') }}
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @if (Session::has('short_url'))
                <div class="alert alert-info">
                    <strong>{{ __('crm.tracking_links.alerts.short_link') }}</strong>
                    <a href="{{ Session::get('short_url') }}" target="_blank" rel="noopener">{{ Session::get('short_url') }}</a>
                </div>
            @endif
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-link"></i> {{ __('crm.tracking_links.form.title') }}</h3>
            </div>
            <form method="POST" action="{{ route('tracking-links.store') }}">
                @csrf
                <div class="card-body">
                    @if(\App\Support\Branch\BranchAccess::canCrossBranches(auth()->user()))
                    <div class="form-group"><label for="tracking-branch">{{ __('crm.tracking_links.form.branch') }}</label>
                        <select id="tracking-branch" name="branch_id" class="form-control" required>
                            @foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">{{ __('crm.tracking_links.form.link_name') }}</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="{{ __('crm.tracking_links.form.link_name_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_url">{{ __('crm.tracking_links.form.landing_url') }}</label>
                                <input type="url" id="base_url" name="base_url" value="{{ old('base_url', $baseUrl) }}" class="form-control tracking-field" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_source">
                                    {{ __('crm.tracking_links.form.source') }}
                                    <i class="fas fa-circle-info text-muted ml-1" data-toggle="tooltip"
                                        title="{{ __('crm.tracking_links.form.source_tooltip') }}"></i>
                                </label>
                                <select id="utm_source" name="utm_source" class="form-control tracking-field" required>
                                    <option value="">{{ __('crm.tracking_links.form.select_source') }}</option>
                                    @foreach(['facebook', 'google', 'tiktok', 'youtube', 'line', 'email', 'referral', 'organic'] as $source)
                                        <option value="{{ $source }}" @selected(old('utm_source') === $source)>{{ __('crm.tracking_links.sources.' . $source) }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">{{ __('crm.tracking_links.form.source_help') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_medium">
                                    {{ __('crm.tracking_links.form.medium') }}
                                    <i class="fas fa-circle-info text-muted ml-1" data-toggle="tooltip"
                                        title="{{ __('crm.tracking_links.form.medium_tooltip') }}"></i>
                                </label>
                                <select id="utm_medium" name="utm_medium" class="form-control tracking-field" required>
                                    <option value="">{{ __('crm.tracking_links.form.select_medium') }}</option>
                                    @foreach(['cpc', 'paid_social', 'organic_social', 'email', 'referral', 'display'] as $medium)
                                        <option value="{{ $medium }}" @selected(old('utm_medium') === $medium)>{{ __('crm.tracking_links.mediums.' . $medium) }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">{{ __('crm.tracking_links.form.medium_help') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_campaign">{{ __('crm.tracking_links.form.campaign') }}</label>
                                <input type="text" id="utm_campaign" name="utm_campaign" value="{{ old('utm_campaign') }}" class="form-control tracking-field" placeholder="{{ __('crm.tracking_links.form.campaign_placeholder') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="utm_content">{{ __('crm.tracking_links.form.content') }}</label>
                                <input type="text" id="utm_content" name="utm_content" value="{{ old('utm_content') }}" class="form-control tracking-field" placeholder="{{ __('crm.tracking_links.form.content_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="utm_term">{{ __('crm.tracking_links.form.term') }}</label>
                                <input type="text" id="utm_term" name="utm_term" value="{{ old('utm_term') }}" class="form-control tracking-field" placeholder="{{ __('crm.tracking_links.form.term_placeholder') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="generated-preview">{{ __('crm.tracking_links.form.preview') }}</label>
                        <div class="input-group">
                            <textarea id="generated-preview" class="form-control" rows="2" readonly></textarea>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary copy-link-btn" data-copy-target="generated-preview" title="{{ __('crm.tracking_links.form.copy_link') }}">
                                    <i class="fas fa-copy"></i> {{ __('crm.tracking_links.form.copy') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-magic"></i> {{ __('crm.tracking_links.form.generate_save') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> {{ __('crm.tracking_links.history.title') }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('crm.tracking_links.history.name') }}</th>
                                <th>{{ __('crm.tracking_links.history.source') }}</th>
                                <th>{{ __('crm.tracking_links.history.medium') }}</th>
                                <th>{{ __('crm.tracking_links.history.campaign') }}</th>
                                <th>{{ __('crm.tracking_links.history.content') }}</th>
                                <th>{{ __('crm.tracking_links.history.created_by') }}</th>
                                <th>{{ __('crm.tracking_links.history.created_at') }}</th>
                                <th>{{ __('crm.tracking_links.history.leads') }}</th>
                                <th>{{ __('crm.tracking_links.history.short_link') }}</th>
                                <th>{{ __('crm.tracking_links.history.link') }}</th>
                                <th>{{ __('crm.tracking_links.history.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($links as $link)
                                <tr>
                                    <td>{{ $link->name }}</td>
                                    <td>{{ $link->utm_source }}</td>
                                    <td>{{ $link->utm_medium }}</td>
                                    <td>{{ $link->utm_campaign }}</td>
                                    <td>{{ $link->utm_content ?: '-' }}</td>
                                    <td>{{ $link->creator->name ?? __('crm.tracking_links.history.system') }}</td>
                                    <td>{{ optional($link->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('crm.leads.index', ['tracking_link_id' => $link->id]) }}" class="badge badge-info">
                                            {{ $link->leads_count }}
                                        </a>
                                    </td>
                                    <td style="min-width: 220px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="short-tracking-link-{{ $link->id }}" value="{{ $link->short_url }}" class="form-control" readonly>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-success copy-link-btn" data-copy-target="short-tracking-link-{{ $link->id }}" title="{{ __('crm.tracking_links.history.copy_short_link') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 320px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="tracking-link-{{ $link->id }}" value="{{ $link->generated_url }}" class="form-control" readonly>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary copy-link-btn" data-copy-target="tracking-link-{{ $link->id }}" title="{{ __('crm.tracking_links.history.copy_link') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('tracking-links.destroy', $link) }}" onsubmit="return confirm(@json(__('crm.tracking_links.history.delete_confirmation')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('crm.tracking_links.history.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">{{ __('crm.tracking_links.history.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $links->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && typeof window.jQuery.fn.tooltip === 'function') {
            window.jQuery('[data-toggle="tooltip"]').tooltip();
        }

        const preview = document.getElementById('generated-preview');
        const trackedFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        const invalidUrlMessage = @json(__('crm.tracking_links.javascript.invalid_url'));
        const copiedLabel = @json(__('crm.tracking_links.javascript.copied'));

        function normalizeTag(value) {
            return value.trim().toLowerCase().replace(/[^a-z0-9ก-๙._-]+/gu, '_');
        }

        function updatePreview() {
            const baseUrl = document.getElementById('base_url').value.trim();
            if (!baseUrl) {
                preview.value = '';
                return;
            }

            try {
                const url = new URL(baseUrl);
                trackedFields.forEach(function (field) {
                    const value = normalizeTag(document.getElementById(field).value);
                    if (value) {
                        url.searchParams.set(field, value);
                    } else {
                        url.searchParams.delete(field);
                    }
                });
                preview.value = url.toString();
            } catch (error) {
                preview.value = invalidUrlMessage;
            }
        }

        document.querySelectorAll('.tracking-field').forEach(function (field) {
            field.addEventListener('input', updatePreview);
            field.addEventListener('change', updatePreview);
        });

        document.querySelectorAll('.copy-link-btn').forEach(function (button) {
            button.addEventListener('click', async function () {
                const target = document.getElementById(button.dataset.copyTarget);
                if (!target || !target.value || target.value.startsWith(invalidUrlMessage)) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(target.value);
                } catch (error) {
                    target.select();
                    document.execCommand('copy');
                }

                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> ' + copiedLabel;
                setTimeout(function () {
                    button.innerHTML = originalHtml;
                }, 1500);
            });
        });

        updatePreview();
    });
</script>
@endsection
