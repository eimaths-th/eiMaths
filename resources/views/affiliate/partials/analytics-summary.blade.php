@if (($analytics_summary ?? null) !== null)
    <section class="affiliate-portal affiliate-analytics-summary card card-primary affiliate-card mb-4" data-affiliate-analytics-summary data-endpoint="{{ route('affiliate.analytics.data') }}" aria-labelledby="affiliate-analytics-summary-title">
        <div class="card-header">
            <h2 id="affiliate-analytics-summary-title" class="card-title">{{ __('affiliate.dashboard.analytics_summary') }}</h2>
        </div>
        <div class="card-body">
            <p>{{ __('affiliate.dashboard.evidence_window', ['from' => $analytics_summary['range']['from'], 'to' => $analytics_summary['range']['to']]) }}</p>
            <dl class="affiliate-analytics-kpis mb-3">
                <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.total_clicks') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.click_evidence_label') }}</small></dt><dd data-affiliate-kpi="total_clicks">{{ $analytics_summary['kpis']['total_clicks'] }}</dd></div>
                <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.unique_clicks') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.click_evidence_label') }}</small></dt><dd data-affiliate-kpi="unique_clicks">{{ $analytics_summary['kpis']['unique_clicks'] }}</dd></div>
                <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.confirmed_sales') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="confirmed_sales">{{ $analytics_summary['kpis']['confirmed_sales'] }}</dd></div>
                <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.eligible_sales_value') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="eligible_sales_value">{{ $analytics_summary['kpis']['eligible_sales_value'] }}</dd></div>
                <div class="affiliate-kpi"><dt>{{ __('affiliate.dashboard.net_commission_income') }} <small class="affiliate-evidence-label">{{ __('affiliate.analytics.sales_evidence_label') }}</small></dt><dd data-affiliate-kpi="net_commission_income">{{ $analytics_summary['kpis']['net_commission_income'] }}</dd></div>
            </dl>
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <p class="text-muted mb-2">{{ __('affiliate.dashboard.last_updated', ['generated_at' => '']) }}<time data-affiliate-generated-at datetime="{{ $analytics_summary['generated_at'] }}">{{ $analytics_summary['generated_at'] }}</time></p>
                <p class="mb-2" role="status" aria-live="polite" data-affiliate-analytics-live-warning></p>
            </div>
            <div class="affiliate-form-actions">
                <button class="btn btn-primary" type="button" data-affiliate-analytics-refresh>{{ __('affiliate.dashboard.refresh') }}</button>
                <a class="btn btn-outline-primary" href="{{ route('affiliate.analytics.index') }}">{{ __('affiliate.dashboard.open_analytics') }}</a>
                <button class="btn btn-outline-secondary" type="button" data-affiliate-copy-referral-link data-affiliate-copy-value="{{ $referral_url }}" data-affiliate-referral-url="{{ $referral_url }}" data-affiliate-copy-success="{{ __('affiliate.dashboard.referral_link_copied') }}" data-affiliate-copy-unavailable="{{ __('affiliate.dashboard.copy_unavailable') }}">
                    {{ __('affiliate.dashboard.copy_referral_link') }}
                </button>
            </div>
        </div>
        <script id="affiliate-analytics-summary-json" type="application/json" data-affiliate-analytics-json>@json($analytics_summary)</script>
        @php
            $analytics_translations = [
                'refresh_warning' => __('affiliate.analytics.refresh_warning'),
                'referral_code_copied' => __('affiliate.dashboard.referral_code_copied'),
                'referral_link_copied' => __('affiliate.dashboard.referral_link_copied'),
                'copy_unavailable' => __('affiliate.dashboard.copy_unavailable'),
            ];
        @endphp
        <script id="affiliate-analytics-summary-translations" type="application/json" data-affiliate-analytics-translations>@json($analytics_translations)</script>
    </section>
@endif
