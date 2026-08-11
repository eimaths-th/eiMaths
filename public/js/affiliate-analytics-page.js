import { CreateAffiliateAnalyticsPoller } from './affiliate-analytics-poller.js';
import { CopyText } from './affiliate-clipboard.js';

export function ParseAffiliateAnalyticsPayload(payload) {
    if (!payload || typeof payload !== 'object' || !payload.range || !payload.kpis || !Array.isArray(payload.daily_series) || !Array.isArray(payload.recent_sales) || !Array.isArray(payload.top_media) || !payload.sales_pagination) {
        throw new Error('Invalid Affiliate analytics payload.');
    }
    const required_kpis = ['total_clicks', 'unique_clicks', 'confirmed_sales', 'eligible_sales_value', 'net_commission_income', 'conversion_rate'];
    if (required_kpis.some((key) => !(key in payload.kpis))) throw new Error('Invalid Affiliate analytics payload KPI shape.');
    return payload;
}

export function FormatAffiliateCount(value, locale = 'en-US') {
    const number = Number(value);
    if (!Number.isFinite(number)) return String(value ?? '0');
    try { return new Intl.NumberFormat(locale).format(number); } catch { return String(Math.trunc(number)); }
}

export function FormatAffiliateMoney(value, locale = 'en-US') {
    if (typeof value !== 'string' || !/^-?\d+(?:\.\d{1,2})?$/.test(value)) return String(value ?? '0.00');
    const number = Number(value);
    if (!Number.isFinite(number)) return value;
    try { return new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number); } catch { return Number(number).toFixed(2); }
}

export function FormatAffiliatePercentage(value, locale = 'en-US') {
    const number = Number(value);
    if (!Number.isFinite(number)) return `${String(value ?? '0.00')}%`;
    try { return `${new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number)}%`; } catch { return `${number.toFixed(2)}%`; }
}

export function InitializeAffiliateAnalyticsPage(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document?.querySelector('[data-affiliate-analytics-page]');
    if (!root) return null;
    const locale = options.Locale ?? options.locale ?? globalThis.document?.documentElement?.lang ?? 'en-US';
    const initial_payload = options.Payload ?? options.payload ?? ReadEmbeddedPayload(root);
    const translations = options.Translations ?? options.translations ?? ReadEmbeddedTranslations(root);
    let current_payload = ParseAffiliateAnalyticsPayload(initial_payload);
    let chart = null;

    const render = (payload) => {
        current_payload = ParseAffiliateAnalyticsPayload(payload);
        RenderKpis(root, current_payload, locale);
        RenderDailyTable(root, current_payload, locale);
        RenderSalesTable(root, current_payload, locale);
        RenderMediaTable(root, current_payload, locale);
        RenderPagination(root, current_payload);
        RenderGeneratedAt(root, current_payload);
        chart = RenderChart(root, current_payload, chart, options.Chart ?? globalThis.Chart, translations.clicks_label ?? 'Clicks');
    };
    render(current_payload);

    const poller = CreateAnalyticsPoller(root, () => current_payload, render, options, translations);

    root.querySelectorAll('[data-affiliate-analytics-refresh]').forEach((button) => button.addEventListener('click', () => poller.RetryNow()));
    root.querySelectorAll('[data-analytics-preset]').forEach((button) => button.addEventListener('click', () => {
        const days = Number(button.dataset.analyticsPreset);
        if (![7, 30, 90].includes(days)) return;
        const to = new Date();
        const from = new Date(to.getTime() - (days - 1) * 86400000);
        const from_input = root.querySelector('[name="from"]');
        const to_input = root.querySelector('[name="to"]');
        if (from_input) from_input.value = ToIsoDate(from);
        if (to_input) to_input.value = ToIsoDate(to);
        poller.RetryNow();
    }));

    if (options.Start !== false) poller.Start();
    return { Poller: poller, Render: render, GetPayload: () => current_payload };
}

export function InitializeAffiliateReferralCopyActions(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;
    const translations = options.Translations ?? options.translations ?? ReadEmbeddedTranslations(root);
    BindAffiliateReferralCopyActions(root, translations, options);
    return { Root: root };
}

export function InitializeAffiliateAnalyticsSummary(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document?.querySelector('[data-affiliate-analytics-summary]');
    if (!root) return null;
    const locale = options.Locale ?? options.locale ?? globalThis.document?.documentElement?.lang ?? 'en-US';
    const initial_payload = options.Payload ?? options.payload ?? ReadEmbeddedPayload(root);
    const translations = options.Translations ?? options.translations ?? ReadEmbeddedTranslations(root);
    let current_payload = ParseAffiliateAnalyticsPayload(initial_payload);

    const render = (payload) => {
        current_payload = ParseAffiliateAnalyticsPayload(payload);
        RenderKpis(root, current_payload, locale);
        RenderGeneratedAt(root, current_payload);
    };
    render(current_payload);

    const poller = CreateAnalyticsPoller(root, () => current_payload, render, options, translations);
    root.querySelectorAll('[data-affiliate-analytics-refresh]').forEach((button) => button.addEventListener('click', () => poller.RetryNow()));
    BindAffiliateReferralCopyActions(root.parentElement ?? root, translations, options);
    if (options.Start !== false) poller.Start();

    return { Poller: poller, Render: render, GetPayload: () => current_payload };
}

function BindAffiliateReferralCopyActions(root, translations, options) {
    if (!root?.querySelectorAll) return;
    const copy_translations = translations ?? {};
    const navigator_object = options.Navigator ?? options.navigator;
    const fallback = options.Fallback ?? options.fallback;
    const feedback = root.querySelector?.('[data-affiliate-referral-feedback]');
    const bind = (selector, success_message) => root.querySelectorAll(selector).forEach((button) => {
        button.addEventListener('click', async () => {
            const value = String(button.dataset?.affiliateCopyValue ?? '');
            const localized_success_message = button.dataset?.affiliateCopySuccess ?? success_message;
            const unavailable_message = button.dataset?.affiliateCopyUnavailable ?? copy_translations.copy_unavailable ?? 'Copy is unavailable.';
            if (!value) {
                if (feedback) feedback.textContent = unavailable_message;
                return;
            }
            const previously_disabled = button.disabled;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            try {
                await CopyText(value, { Navigator: navigator_object, Fallback: fallback });
                if (feedback) feedback.textContent = localized_success_message;
            } catch {
                if (feedback) feedback.textContent = unavailable_message;
            } finally {
                button.disabled = previously_disabled;
                button.removeAttribute('aria-busy');
                button.focus?.();
            }
        });
    });

    bind('[data-affiliate-copy-referral-code]', copy_translations.referral_code_copied ?? 'Referral code copied.');
    bind('[data-affiliate-copy-referral-link]', copy_translations.referral_link_copied ?? 'Referral link copied.');
}

function CreateAnalyticsPoller(root, GetPayload, Render, options, translations) {
    const endpoint = options.Endpoint ?? options.endpoint ?? root.dataset.endpoint ?? root.dataset.dataEndpoint ?? '/affiliate/analytics/data';
    const fetcher = options.Fetch ?? options.fetch ?? globalThis.fetch;

    return CreateAffiliateAnalyticsPoller({
        Fetch: ({ signal }) => fetcher(
            BuildEndpoint(endpoint, GetPayload().range, GetPayload().sales_pagination.current_page),
            { headers: { Accept: 'application/json' }, signal },
        ).then((response) => response),
        HandleData: (payload) => { Render(payload.data ?? payload); SetLiveMessage(root, ''); },
        HandleError: () => SetLiveMessage(root, translations.refresh_warning ?? 'Analytics refresh is temporarily unavailable.'),
        ClearWarning: () => SetLiveMessage(root, ''),
        SetTimeout: options.SetTimeout,
        ClearTimeout: options.ClearTimeout,
        IsHidden: options.IsHidden,
        AddVisibilityListener: options.AddVisibilityListener,
        RemoveVisibilityListener: options.RemoveVisibilityListener,
    });
}

function ReadEmbeddedPayload(root) {
    const node = root.querySelector('[data-affiliate-analytics-json]');
    if (!node) throw new Error('Affiliate analytics payload is missing.');
    return JSON.parse(node.textContent ?? '{}');
}

function ReadEmbeddedTranslations(root) {
    const node = root.querySelector('[data-affiliate-analytics-translations]');
    if (!node) return {};
    try {
        const translations = JSON.parse(node.textContent ?? '{}');
        return translations && typeof translations === 'object' ? translations : {};
    } catch {
        return {};
    }
}

function RenderKpis(root, payload, locale) {
    const values = {
        total_clicks: FormatAffiliateCount(payload.kpis.total_clicks, locale),
        unique_clicks: FormatAffiliateCount(payload.kpis.unique_clicks, locale),
        confirmed_sales: FormatAffiliateCount(payload.kpis.confirmed_sales, locale),
        eligible_sales_value: FormatAffiliateMoney(payload.kpis.eligible_sales_value, locale),
        net_commission_income: FormatAffiliateMoney(payload.kpis.net_commission_income, locale),
        conversion_rate: FormatAffiliatePercentage(payload.kpis.conversion_rate, locale),
    };
    Object.entries(values).forEach(([key, value]) => {
        const node = root.querySelector(`[data-affiliate-kpi="${key}"]`);
        if (node) node.textContent = value;
    });
}

function RenderDailyTable(root, payload, locale) {
    const body = root.querySelector('[data-affiliate-daily-body]');
    if (!body) return;
    body.replaceChildren();
    payload.daily_series.forEach((day) => {
        const row = body.ownerDocument.createElement('tr');
        [day.metric_date, FormatAffiliateCount(day.total_clicks, locale), FormatAffiliateCount(day.unique_clicks, locale), FormatAffiliateCount(day.confirmed_sales, locale), FormatAffiliateMoney(day.net_commission_income, locale)].forEach((value) => {
            const cell = body.ownerDocument.createElement('td');
            cell.textContent = value;
            row.append(cell);
        });
        body.append(row);
    });
}

function RenderSalesTable(root, payload, locale) {
    const body = root.querySelector('[data-affiliate-sales-body]');
    if (!body) return;
    body.replaceChildren();
    payload.recent_sales.forEach((sale) => {
        const row = body.ownerDocument.createElement('tr');
        [sale.commission_id, sale.confirmed_at, sale.status, sale.reversal_status, FormatAffiliateMoney(sale.net_commission, locale)].forEach((value) => {
            const cell = body.ownerDocument.createElement('td');
            cell.textContent = String(value ?? '');
            row.append(cell);
        });
        body.append(row);
    });
}

function RenderMediaTable(root, payload, locale) {
    const body = root.querySelector('[data-affiliate-media-body]');
    if (!body) return;
    body.replaceChildren();
    payload.top_media.forEach((media) => {
        const row = body.ownerDocument.createElement('tr');
        [media.title ?? media.campaign_code, FormatAffiliateCount(media.total_clicks, locale), FormatAffiliateCount(media.confirmed_sales, locale), FormatAffiliateMoney(media.net_commission_income, locale)].forEach((value) => {
            const cell = body.ownerDocument.createElement('td');
            cell.textContent = String(value ?? '');
            row.append(cell);
        });
        body.append(row);
    });
}

function RenderPagination(root, payload) {
    root.querySelectorAll('[data-affiliate-sales-page]').forEach((node) => { node.textContent = `${payload.sales_pagination.current_page} / ${payload.sales_pagination.last_page}`; });
}

function RenderGeneratedAt(root, payload) {
    root.querySelectorAll('[data-affiliate-generated-at]').forEach((node) => {
        node.textContent = payload.generated_at;
        node.setAttribute('datetime', payload.generated_at);
    });
}

function RenderChart(root, payload, previous_chart, ChartConstructor, clicks_label) {
    if (previous_chart?.destroy) previous_chart.destroy();
    const canvas = root.querySelector('[data-affiliate-daily-chart]');
    if (!canvas || typeof ChartConstructor !== 'function') return null;
    return new ChartConstructor(canvas.getContext('2d'), {
        type: 'line',
        data: { labels: payload.daily_series.map((day) => day.metric_date), datasets: [{ label: clicks_label, data: payload.daily_series.map((day) => day.total_clicks) }] },
        options: { animation: globalThis.matchMedia?.('(prefers-reduced-motion: reduce)').matches ? false : undefined },
    });
}

function SetLiveMessage(root, message) {
    root.querySelectorAll('[data-affiliate-analytics-live-warning]').forEach((node) => { node.textContent = message; });
}

function BuildEndpoint(endpoint, range, page) {
    const url = new URL(endpoint, globalThis.location?.origin ?? 'http://localhost');
    url.searchParams.set('from', range.from);
    url.searchParams.set('to', range.to);
    url.searchParams.set('sales_page', String(page));
    return url.toString();
}

function ToIsoDate(date) {
    return date.toISOString().slice(0, 10);
}
