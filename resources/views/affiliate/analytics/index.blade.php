@extends('layouts.app')

@section('content')
<main class="affiliate-portal" data-affiliate-analytics-page data-endpoint="{{ route('affiliate.analytics.data') }}" aria-labelledby="affiliate-analytics-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-analytics-title" class="mb-0">{{ __('affiliate.analytics.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-analytics-range-title">
                <div class="card-header">
                    <h2 id="affiliate-analytics-range-title" class="card-title">{{ __('affiliate.analytics.key_evidence') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('affiliate.analytics.evidence_window', ['from' => $analytics['range']['from'], 'to' => $analytics['range']['to'], 'timezone' => $analytics['range']['timezone']]) }}</p>
                    <form class="affiliate-filter-form" data-affiliate-analytics-range method="GET" action="{{ route('affiliate.analytics.index') }}">
                        <div class="form-group affiliate-date-field">
                            <label for="affiliate-analytics-from">{{ __('affiliate.analytics.from') }}</label>
                            <input class="form-control" id="affiliate-analytics-from" name="from" type="date" value="{{ $analytics['range']['from'] }}" required>
                        </div>
                        <div class="form-group affiliate-date-field">
                            <label for="affiliate-analytics-to">{{ __('affiliate.analytics.to') }}</label>
                            <input class="form-control" id="affiliate-analytics-to" name="to" type="date" value="{{ $analytics['range']['to'] }}" required>
                        </div>
                        <div class="affiliate-preset-actions">
                            <button class="btn btn-outline-primary" type="button" data-analytics-preset="7">{{ __('affiliate.analytics.preset_7') }}</button>
                            <button class="btn btn-outline-primary" type="button" data-analytics-preset="30">{{ __('affiliate.analytics.preset_30') }}</button>
                            <button class="btn btn-outline-primary" type="button" data-analytics-preset="90">{{ __('affiliate.analytics.preset_90') }}</button>
                            <button class="btn btn-primary" type="submit">{{ __('affiliate.analytics.apply') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-analytics-kpis-title">
                <div class="card-header">
                    <h2 id="affiliate-analytics-kpis-title" class="card-title">{{ __('affiliate.analytics.key_evidence') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="affiliate-analytics-kpis mb-3">
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.analytics.clicks') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.click_evidence_label') }}</small></dt><dd data-affiliate-kpi="total_clicks">{{ $analytics['kpis']['total_clicks'] }}</dd></div>
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.analytics.unique_clicks') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.click_evidence_label') }}</small></dt><dd data-affiliate-kpi="unique_clicks">{{ $analytics['kpis']['unique_clicks'] }}</dd></div>
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.analytics.confirmed_sales') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="confirmed_sales">{{ $analytics['kpis']['confirmed_sales'] }}</dd></div>
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.eligible_sales_value') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="eligible_sales_value">{{ $analytics['kpis']['eligible_sales_value'] }}</dd></div>
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.net_commission_income') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="net_commission_income">{{ $analytics['kpis']['net_commission_income'] }}</dd></div>
                        <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.conversion_rate') }}</dt><dd data-affiliate-kpi="conversion_rate">{{ $analytics['kpis']['conversion_rate'] }}%</dd></div>
                    </dl>
                    <p class="text-muted mb-0">{{ __('affiliate.analytics.conversion_help') }}</p>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-daily-title">
                <div class="card-header">
                    <h2 id="affiliate-daily-title" class="card-title">{{ __('affiliate.analytics.daily_evidence') }}</h2>
                </div>
                <div class="card-body">
                    <div class="affiliate-chart-container mb-4">
                        <canvas data-affiliate-daily-chart aria-label="{{ __('affiliate.analytics.daily_evidence') }}"></canvas>
                    </div>
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <caption class="sr-only">{{ __('affiliate.analytics.daily_table_caption') }}</caption>
                            <thead><tr><th scope="col">{{ __('affiliate.analytics.date') }}</th><th scope="col">{{ __('affiliate.analytics.clicks') }}</th><th scope="col">{{ __('affiliate.analytics.unique_clicks') }}</th><th scope="col">{{ __('affiliate.analytics.confirmed_sales') }}</th><th scope="col">{{ __('affiliate.analytics.net_income') }}</th></tr></thead>
                            <tbody data-affiliate-daily-body>
                                @foreach ($analytics['daily_series'] as $day)<tr><td>{{ $day['metric_date'] }}</td><td>{{ $day['total_clicks'] }}</td><td>{{ $day['unique_clicks'] }}</td><td>{{ $day['confirmed_sales'] }}</td><td>{{ $day['net_commission_income'] }}</td></tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-sales-title">
                <div class="card-header">
                    <h2 id="affiliate-sales-title" class="card-title">{{ __('affiliate.analytics.recent_sales') }}</h2>
                </div>
                <div class="card-body">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <caption class="sr-only">{{ __('affiliate.analytics.sales_table_caption') }}</caption>
                            <thead><tr><th scope="col">{{ __('affiliate.analytics.id') }}</th><th scope="col">{{ __('affiliate.analytics.confirmed') }}</th><th scope="col">{{ __('affiliate.analytics.status') }}</th><th scope="col">{{ __('affiliate.analytics.reversal') }}</th><th scope="col">{{ __('affiliate.analytics.net_commission') }}</th></tr></thead>
                            <tbody data-affiliate-sales-body>
                                @foreach ($analytics['recent_sales'] as $sale)<tr><td>{{ $sale['commission_id'] }}</td><td>{{ $sale['confirmed_at'] }}</td><td>{{ $sale['status'] }}</td><td>{{ $sale['reversal_status'] }}</td><td>{{ $sale['net_commission'] }}</td></tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mt-3 mb-0" data-affiliate-sales-page>{{ $analytics['sales_pagination']['current_page'] }} / {{ $analytics['sales_pagination']['last_page'] }}</p>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-analytics-media-title">
                <div class="card-header">
                    <h2 id="affiliate-analytics-media-title" class="card-title">{{ __('affiliate.analytics.top_media') }}</h2>
                </div>
                <div class="card-body">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <caption class="sr-only">{{ __('affiliate.analytics.media_table_caption') }}</caption>
                            <thead><tr><th scope="col">{{ __('affiliate.analytics.campaign') }}</th><th scope="col">{{ __('affiliate.analytics.clicks') }}</th><th scope="col">{{ __('affiliate.analytics.confirmed_sales') }}</th><th scope="col">{{ __('affiliate.analytics.net_income') }}</th></tr></thead>
                            <tbody data-affiliate-media-body>
                                @foreach ($analytics['top_media'] as $media)<tr><td>{{ $media['title'] ?: $media['campaign_code'] }}</td><td>{{ $media['total_clicks'] }}</td><td>{{ $media['confirmed_sales'] }}</td><td>{{ $media['net_commission_income'] }}</td></tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="affiliate-form-actions mb-4">
                <p class="text-muted mb-0">{{ __('affiliate.analytics.generated_at') }}: <time data-affiliate-generated-at datetime="{{ $analytics['generated_at'] }}">{{ $analytics['generated_at'] }}</time></p>
                <p class="mb-0" role="status" aria-live="polite" data-affiliate-analytics-live-warning></p>
                <button class="btn btn-primary" type="button" data-affiliate-analytics-refresh>{{ __('affiliate.analytics.refresh') }}</button>
            </div>
        </div>
    </section>

    <script id="affiliate-analytics-json" type="application/json" data-affiliate-analytics-json>@json($analytics)</script>
    <script id="affiliate-analytics-translations" type="application/json" data-affiliate-analytics-translations>@json([
        'refresh_warning' => __('affiliate.analytics.refresh_warning'),
        'clicks_label' => __('affiliate.analytics.clicks'),
    ])</script>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection

@section('script')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script type="module" src="{{ asset('js/affiliate-analytics-page.js') }}"></script>
<script type="module">import { InitializeAffiliateAnalyticsPage } from '{{ asset('js/affiliate-analytics-page.js') }}'; InitializeAffiliateAnalyticsPage();</script>
@endsection
