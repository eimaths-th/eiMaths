@extends('layouts.app')

@section('content')
<main class="affiliate-portal" data-affiliate-analytics-page data-endpoint="{{ route('affiliate.analytics.data') }}" aria-labelledby="affiliate-analytics-title">
    @include('affiliate.partials.navigation')
    <h1 id="affiliate-analytics-title">Affiliate analytics</h1>
    <p>Evidence window: {{ $analytics['range']['from'] }} to {{ $analytics['range']['to'] }} ({{ $analytics['range']['timezone'] }})</p>
    <form data-affiliate-analytics-range method="GET" action="{{ route('affiliate.analytics.index') }}">
        <label for="affiliate-analytics-from">From</label>
        <input id="affiliate-analytics-from" name="from" type="date" value="{{ $analytics['range']['from'] }}" required>
        <label for="affiliate-analytics-to">To</label>
        <input id="affiliate-analytics-to" name="to" type="date" value="{{ $analytics['range']['to'] }}" required>
        <button type="button" data-analytics-preset="7">7 days</button>
        <button type="button" data-analytics-preset="30">30 days</button>
        <button type="button" data-analytics-preset="90">90 days</button>
        <button type="submit">Apply</button>
    </form>
    <section aria-labelledby="affiliate-analytics-kpis-title">
        <h2 id="affiliate-analytics-kpis-title">Key evidence</h2>
        <dl>
            <div><dt>Total clicks</dt><dd data-affiliate-kpi="total_clicks">{{ $analytics['kpis']['total_clicks'] }}</dd></div>
            <div><dt>Unique clicks</dt><dd data-affiliate-kpi="unique_clicks">{{ $analytics['kpis']['unique_clicks'] }}</dd></div>
            <div><dt>Confirmed sales</dt><dd data-affiliate-kpi="confirmed_sales">{{ $analytics['kpis']['confirmed_sales'] }}</dd></div>
            <div><dt>Eligible sales value</dt><dd data-affiliate-kpi="eligible_sales_value">{{ $analytics['kpis']['eligible_sales_value'] }}</dd></div>
            <div><dt>Net commission income</dt><dd data-affiliate-kpi="net_commission_income">{{ $analytics['kpis']['net_commission_income'] }}</dd></div>
            <div><dt>Conversion rate</dt><dd data-affiliate-kpi="conversion_rate">{{ $analytics['kpis']['conversion_rate'] }}%</dd></div>
        </dl>
    </section>
    <section aria-labelledby="affiliate-daily-title">
        <h2 id="affiliate-daily-title">Daily evidence</h2>
        <canvas data-affiliate-daily-chart aria-label="Daily affiliate evidence chart"></canvas>
        <table><caption>Daily evidence table</caption><thead><tr><th>Date</th><th>Clicks</th><th>Unique clicks</th><th>Confirmed sales</th><th>Net income</th></tr></thead><tbody data-affiliate-daily-body>
            @foreach ($analytics['daily_series'] as $day)<tr><td>{{ $day['metric_date'] }}</td><td>{{ $day['total_clicks'] }}</td><td>{{ $day['unique_clicks'] }}</td><td>{{ $day['confirmed_sales'] }}</td><td>{{ $day['net_commission_income'] }}</td></tr>@endforeach
        </tbody></table>
    </section>
    <section aria-labelledby="affiliate-sales-title">
        <h2 id="affiliate-sales-title">Recent confirmed sales</h2>
        <table><caption>Receipt-backed affiliate sales</caption><thead><tr><th>ID</th><th>Confirmed</th><th>Status</th><th>Reversal</th><th>Net commission</th></tr></thead><tbody data-affiliate-sales-body>
            @foreach ($analytics['recent_sales'] as $sale)<tr><td>{{ $sale['commission_id'] }}</td><td>{{ $sale['confirmed_at'] }}</td><td>{{ $sale['status'] }}</td><td>{{ $sale['reversal_status'] }}</td><td>{{ $sale['net_commission'] }}</td></tr>@endforeach
        </tbody></table>
        <p data-affiliate-sales-page>{{ $analytics['sales_pagination']['current_page'] }} / {{ $analytics['sales_pagination']['last_page'] }}</p>
    </section>
    <section aria-labelledby="affiliate-media-title">
        <h2 id="affiliate-media-title">Top media</h2>
        <table><caption>Campaign performance</caption><thead><tr><th>Campaign</th><th>Clicks</th><th>Sales</th><th>Net income</th></tr></thead><tbody data-affiliate-media-body>
            @foreach ($analytics['top_media'] as $media)<tr><td>{{ $media['title'] ?: $media['campaign_code'] }}</td><td>{{ $media['total_clicks'] }}</td><td>{{ $media['confirmed_sales'] }}</td><td>{{ $media['net_commission_income'] }}</td></tr>@endforeach
        </tbody></table>
    </section>
    <p><time data-affiliate-generated-at datetime="{{ $analytics['generated_at'] }}">{{ $analytics['generated_at'] }}</time></p>
    <p role="status" aria-live="polite" data-affiliate-analytics-live-warning></p>
    <button type="button" data-affiliate-analytics-refresh>Refresh</button>
    <script id="affiliate-analytics-json" type="application/json" data-affiliate-analytics-json>@json($analytics)</script>
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
