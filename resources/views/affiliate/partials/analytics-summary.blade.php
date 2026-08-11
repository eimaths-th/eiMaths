@if (($analytics_summary ?? null) !== null)
    <section class="affiliate-analytics-summary" data-affiliate-analytics-summary aria-labelledby="affiliate-analytics-summary-title">
        <h2 id="affiliate-analytics-summary-title">Analytics summary</h2>
        <p>Evidence window: {{ $analytics_summary['range']['from'] }} to {{ $analytics_summary['range']['to'] }}</p>
        <dl>
            <div><dt>Total clicks</dt><dd>{{ $analytics_summary['kpis']['total_clicks'] }}</dd></div>
            <div><dt>Unique clicks</dt><dd>{{ $analytics_summary['kpis']['unique_clicks'] }}</dd></div>
            <div><dt>Confirmed sales</dt><dd>{{ $analytics_summary['kpis']['confirmed_sales'] }}</dd></div>
            <div><dt>Eligible sales value</dt><dd>{{ $analytics_summary['kpis']['eligible_sales_value'] }}</dd></div>
            <div><dt>Net commission income</dt><dd>{{ $analytics_summary['kpis']['net_commission_income'] }}</dd></div>
            <div><dt>Conversion rate</dt><dd>{{ $analytics_summary['kpis']['conversion_rate'] }}%</dd></div>
        </dl>
        <p>Last updated: <time datetime="{{ $analytics_summary['generated_at'] }}">{{ $analytics_summary['generated_at'] }}</time></p>
        <p role="status" aria-live="polite" data-affiliate-analytics-live-warning></p>
        <button type="button" data-affiliate-analytics-refresh>Refresh</button>
        <a href="{{ route('affiliate.analytics.index') }}">Open full analytics</a>
        <a href="{{ $referral_url }}" data-affiliate-referral-url>Copy referral link</a>
    </section>
@endif
