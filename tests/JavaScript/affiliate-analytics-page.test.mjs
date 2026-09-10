import test from 'node:test';
import assert from 'node:assert/strict';
import {
    FormatAffiliateMoney,
    InitializeAffiliateAnalyticsPage,
    InitializeAffiliateReferralCopyActions,
    InitializeAffiliateAnalyticsSummary,
    ParseAffiliateAnalyticsPayload,
} from '../../public/js/affiliate-analytics-page.js';

class FakeDocument {
    createElement(tag_name) {
        return new FakeNode({ owner_document: this, tag_name });
    }
}

class FakeNode {
    constructor({ owner_document = new FakeDocument(), dataset = {}, text_content = '', value = '', tag_name = 'div' } = {}) {
        this.ownerDocument = owner_document;
        this.dataset = dataset;
        this.textContent = text_content;
        this.value = value;
        this.tagName = tag_name;
        this.attributes = {};
        this.children = [];
        this.listeners = {};
        this.disabled = false;
        this.innerHTML = '';
    }

    replaceChildren(...children) {
        this.children = children;
    }

    append(child) {
        this.children.push(child);
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    removeAttribute(name) {
        delete this.attributes[name];
    }

    focus() {}

    addEventListener(name, listener) {
        this.listeners[name] = listener;
    }

    querySelector() {
        return null;
    }

    querySelectorAll() {
        return [];
    }
}

class FakeRoot extends FakeNode {
    constructor() {
        super();
        this.selections = new Map();
    }

    setSelection(selector, node_or_nodes) {
        this.selections.set(selector, node_or_nodes);
        return node_or_nodes;
    }

    querySelector(selector) {
        const selection = this.selections.get(selector);
        return Array.isArray(selection) ? selection[0] ?? null : selection ?? null;
    }

    querySelectorAll(selector) {
        const selection = this.selections.get(selector);
        return Array.isArray(selection) ? selection : selection ? [selection] : [];
    }
}

function CreatePayload(version = 'initial') {
    return {
        generated_at: `2026-08-11T00:00:0${version === 'initial' ? '1' : '2'}+00:00`,
        range: { from: '2026-08-01', to: '2026-08-10' },
        kpis: {
            total_clicks: version === 'initial' ? 1234 : 2345,
            unique_clicks: 100,
            confirmed_sales: 12,
            eligible_sales_value: '1000.00',
            net_commission_income: '150.00',
            conversion_rate: '1.20',
        },
        daily_series: [{ metric_date: '2026-08-01', total_clicks: 12, unique_clicks: 10, confirmed_sales: 1, net_commission_income: '15.00' }],
        recent_sales: [{ commission_id: 1, confirmed_at: '2026-08-01T00:00:00+00:00', status: 'confirmed', reversal_status: 'none', net_commission: '15.00' }],
        top_media: [{ title: '<unsafe-title>', campaign_code: 'safe-campaign', total_clicks: 12, confirmed_sales: 1, net_commission_income: '15.00' }],
        sales_pagination: { current_page: 1, last_page: 1, per_page: 25, total: 1 },
    };
}

function CreateAnalyticsRoot(payload = CreatePayload()) {
    const root = new FakeRoot();
    root.dataset.endpoint = '/affiliate/analytics/data';
    const document_object = root.ownerDocument;
    const payload_node = new FakeNode({ owner_document: document_object, text_content: JSON.stringify(payload) });
    const translation_node = new FakeNode({ owner_document: document_object, text_content: JSON.stringify({
        clicks_label: 'Clicks',
        referral_code_copied: 'Referral code copied.',
        referral_link_copied: 'Referral link copied.',
        copy_unavailable: 'Copy unavailable.',
    }) });
    root.setSelection('[data-affiliate-analytics-json]', payload_node);
    root.setSelection('[data-affiliate-analytics-translations]', translation_node);
    root.setSelection('[data-affiliate-analytics-refresh]', []);
    root.setSelection('[data-analytics-preset]', []);
    root.setSelection('[data-affiliate-analytics-live-warning]', [new FakeNode({ owner_document: document_object })]);

    for (const key of ['total_clicks', 'unique_clicks', 'confirmed_sales', 'eligible_sales_value', 'net_commission_income', 'conversion_rate']) {
        root.setSelection(`[data-affiliate-kpi="${key}"]`, new FakeNode({ owner_document: document_object }));
    }
    for (const selector of ['[data-affiliate-daily-body]', '[data-affiliate-sales-body]', '[data-affiliate-media-body]']) {
        root.setSelection(selector, new FakeNode({ owner_document: document_object }));
    }
    root.setSelection('[data-affiliate-sales-page]', [new FakeNode({ owner_document: document_object })]);
    root.setSelection('[data-affiliate-generated-at]', [new FakeNode({ owner_document: document_object })]);
    const canvas = new FakeNode({ owner_document: document_object, tag_name: 'canvas' });
    canvas.getContext = () => ({ context: true });
    root.setSelection('[data-affiliate-daily-chart]', canvas);
    return root;
}

test('Sale status stays translated when live data refreshes', () => {
    const payload = CreatePayload();
    payload.recent_sales[0].status = 'PENDING';
    const root = CreateAnalyticsRoot(payload);
    root.querySelector('[data-affiliate-analytics-translations]').textContent = JSON.stringify({ commission_statuses: { PENDING: 'รอปลดล็อก', AVAILABLE: 'ปลดล็อกแล้ว' } });
    const page = InitializeAffiliateAnalyticsPage({ Root: root, Start: false });
    assert.equal(root.querySelector('[data-affiliate-sales-body]').children[0].children[2].textContent, 'รอปลดล็อก');
    payload.recent_sales[0].status = 'AVAILABLE';
    page.Render(payload);
    assert.equal(root.querySelector('[data-affiliate-sales-body]').children[0].children[2].textContent, 'ปลดล็อกแล้ว');
});

test('RejectsMalformedAnalyticsPayloadsAndFormatsMoneyDeterministically', () => {
    assert.throws(() => ParseAffiliateAnalyticsPayload({}), /Invalid Affiliate analytics payload/);
    assert.equal(FormatAffiliateMoney('1234.5', 'en-US'), '1,234.50');
    assert.equal(FormatAffiliateMoney('not-money', 'en-US'), 'not-money');
});

test('RendersSafeTextAndReplacesThePreviousChart', () => {
    const root = CreateAnalyticsRoot();
    const chart_instances = [];
    function ChartConstructor(context, configuration) {
        this.context = context;
        this.configuration = configuration;
        this.destroyed = false;
        this.destroy = () => { this.destroyed = true; };
        chart_instances.push(this);
    }

    const page = InitializeAffiliateAnalyticsPage({ Root: root, Chart: ChartConstructor, Start: false });
    assert.equal(root.querySelector('[data-affiliate-kpi="total_clicks"]').textContent, '1,234');
    const initial_media_row = root.querySelector('[data-affiliate-media-body]').children[0];
    assert.equal(initial_media_row.children[0].textContent, '<unsafe-title>');
    assert.equal(chart_instances.length, 1);

    page.Render(CreatePayload('updated'));
    assert.equal(chart_instances[0].destroyed, true);
    assert.equal(chart_instances.length, 2);
    assert.equal(chart_instances[1].configuration.data.datasets[0].label, 'Clicks');
    assert.equal(root.querySelector('[data-affiliate-kpi="total_clicks"]').textContent, '2,345');
});

test('KeepsRenderedEvidenceWhenRefreshFailsAndAnnouncesTheWarning', async () => {
    const root = CreateAnalyticsRoot();
    const warning = root.querySelector('[data-affiliate-analytics-live-warning]');
    const fetch_error = new Error('network unavailable');
    const page = InitializeAffiliateAnalyticsPage({
        Root: root,
        Start: false,
        Fetch: async () => { throw fetch_error; },
        SetTimeout: () => 1,
        ClearTimeout: () => {},
    });

    page.Poller.Start();
    await page.Poller.Poll();
    assert.equal(root.querySelector('[data-affiliate-kpi="total_clicks"]').textContent, '1,234');
    assert.match(warning.textContent, /temporarily unavailable/);
});

test('InitializesTheDashboardSummaryWithTheEmbeddedPayload', () => {
    const root = CreateAnalyticsRoot();
    const summary = InitializeAffiliateAnalyticsSummary({ Root: root, Start: false });

    assert.equal(summary.GetPayload().kpis.total_clicks, 1234);
    assert.equal(root.querySelector('[data-affiliate-kpi="confirmed_sales"]').textContent, '12');
    assert.equal(root.querySelector('[data-affiliate-generated-at]').attributes.datetime, '2026-08-11T00:00:01+00:00');
});

test('CopiesTheFullReferralCodeAndRestoresTheCopyAction', async () => {
    const root = CreateAnalyticsRoot();
    const copy_button = new FakeNode({ dataset: { affiliateCopyValue: 'AFF-FULL-CODE' } });
    const feedback = new FakeNode();
    root.setSelection('[data-affiliate-copy-referral-code]', [copy_button]);
    root.setSelection('[data-affiliate-referral-feedback]', [feedback]);
    const copied_values = [];

    InitializeAffiliateAnalyticsSummary({
        Root: root,
        Start: false,
        Navigator: {},
        Fallback: async (value) => copied_values.push(value),
    });

    await copy_button.listeners.click();

    assert.deepEqual(copied_values, ['AFF-FULL-CODE']);
    assert.equal(copy_button.disabled, false);
    assert.equal(copy_button.attributes['aria-busy'], undefined);
    assert.equal(feedback.textContent, 'Referral code copied.');
});

test('CopiesTheFullReferralCodeWithoutAnAnalyticsSummary', async () => {
    const root = new FakeRoot();
    const copy_button = new FakeNode({
        dataset: {
            affiliateCopyValue: 'AFF-FULL-CODE',
            affiliateCopySuccess: 'Referral code copied.',
        },
    });
    const feedback = new FakeNode();
    root.setSelection('[data-affiliate-copy-referral-code]', [copy_button]);
    root.setSelection('[data-affiliate-referral-feedback]', [feedback]);
    const copied_values = [];

    InitializeAffiliateReferralCopyActions({
        Root: root,
        Navigator: {},
        Fallback: async (value) => copied_values.push(value),
    });

    await copy_button.listeners.click();

    assert.deepEqual(copied_values, ['AFF-FULL-CODE']);
    assert.equal(feedback.textContent, 'Referral code copied.');
});
