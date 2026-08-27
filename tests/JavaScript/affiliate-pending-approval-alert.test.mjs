import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CreateAffiliatePendingApprovalAlert,
    CreateAffiliatePendingApprovalSummaryHandler,
    RenderAffiliatePendingApprovalBadgeCount,
    RenderAffiliatePendingApprovalToast,
    ValidateAffiliatePendingApprovalSummary,
} from '../../public/js/affiliate-pending-approval-alert.js';

class FakeNode {
    constructor() {
        this.textContent = '';
        this.hidden = true;
        this.href = '';
        this.attributes = {};
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }
}

class FakeRoot {
    constructor() {
        this.attributes = {
            'data-summary-url': '/admin/affiliates/pending-approval-summary',
            'data-index-url': '/admin/affiliates',
            'data-storage-key': 'affiliate-alert:1',
        };
        this.nodes = new Map([
            ['[data-affiliate-pending-approval-toast]', new FakeNode()],
            ['[data-affiliate-pending-approval-title]', new FakeNode()],
            ['[data-affiliate-pending-approval-message]', new FakeNode()],
            ['[data-affiliate-pending-approval-action]', new FakeNode()],
        ]);
    }

    getAttribute(name) {
        return this.attributes[name] ?? null;
    }

    querySelector(selector) {
        return this.nodes.get(selector) ?? null;
    }
}

test('ValidatesPendingApprovalSummaryAndShowsInitialPendingApplications', async () => {
    const summary = {
        data: {
            pending_count: 2,
            latest_event_cursor: '2026-08-27T04:00:00+00:00|2',
            latest_event_status: 'PENDING_APPROVAL',
            latest_pending_affiliate: {
                id: 2,
                name: 'New Affiliate',
                pending_at: '2026-08-27T04:00:00+00:00',
            },
        },
    };
    const toast_events = [];
    const written_cursors = [];
    const badge_counts = [];
    const handler = CreateAffiliatePendingApprovalSummaryHandler({
        ReadCursor: () => null,
        WriteCursor: (cursor) => written_cursors.push(cursor),
        UpdateBadgeCount: (count) => badge_counts.push(count),
        ShowToast: (event) => toast_events.push(event),
    });

    assert.deepEqual(ValidateAffiliatePendingApprovalSummary(summary), summary.data);
    await handler(summary);

    assert.deepEqual(badge_counts, [2]);
    assert.deepEqual(written_cursors, ['2026-08-27T04:00:00+00:00|2']);
    assert.deepEqual(toast_events, [{
        event_type: 'pending_summary',
        pending_count: 2,
        latest_pending_affiliate: summary.data.latest_pending_affiliate,
    }]);
});

test('RendersAffiliatePendingApprovalBadgeAndNewApplicationToastSafely', () => {
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-pending-approval-badge]' ? [badge] : [],
    };
    const root = new FakeRoot();

    RenderAffiliatePendingApprovalBadgeCount(document_object, 3);
    RenderAffiliatePendingApprovalToast(root, '/admin/affiliates', {
        event_type: 'new_application',
        pending_count: 3,
        latest_pending_affiliate: {
            id: 8,
            name: 'New Affiliate',
            pending_at: '2026-08-27T04:00:00+00:00',
        },
    });

    assert.equal(badge.textContent, '3');
    assert.equal(badge.hidden, false);
    assert.equal(root.querySelector('[data-affiliate-pending-approval-title]').textContent, 'มี Affiliate สมัครใหม่รออนุมัติ');
    assert.match(root.querySelector('[data-affiliate-pending-approval-message]').textContent, /New Affiliate/);
    assert.equal(root.querySelector('[data-affiliate-pending-approval-action]').href, '/admin/affiliates');
    assert.equal(root.querySelector('[data-affiliate-pending-approval-toast]').hidden, false);
});

test('ShowsOnlyAChangedPendingApprovalEventAndIgnoresAnApprovalEvent', async () => {
    let stored_cursor = '2026-08-27T03:00:00+00:00|1';
    const toast_events = [];
    const handler = CreateAffiliatePendingApprovalSummaryHandler({
        ReadCursor: () => stored_cursor,
        WriteCursor: (cursor) => { stored_cursor = cursor; },
        UpdateBadgeCount: () => {},
        ShowToast: (event) => toast_events.push(event),
    });
    const pending_affiliate = {
        id: 2,
        name: 'New Affiliate',
        pending_at: '2026-08-27T04:00:00+00:00',
    };

    await handler({ data: {
        pending_count: 1,
        latest_event_cursor: '2026-08-27T04:00:00+00:00|2',
        latest_event_status: 'PENDING_APPROVAL',
        latest_pending_affiliate: pending_affiliate,
    } });
    await handler({ data: {
        pending_count: 0,
        latest_event_cursor: '2026-08-27T05:00:00+00:00|2',
        latest_event_status: 'ACTIVE',
        latest_pending_affiliate: null,
    } });

    assert.deepEqual(toast_events, [{
        event_type: 'new_application',
        pending_count: 1,
        latest_pending_affiliate: pending_affiliate,
    }]);
    assert.equal(stored_cursor, '2026-08-27T05:00:00+00:00|2');
});

test('RejectsMalformedAffiliatePendingApprovalSummary', () => {
    assert.equal(ValidateAffiliatePendingApprovalSummary({ data: {
        pending_count: 1,
        latest_event_cursor: null,
        latest_event_status: 'PENDING_APPROVAL',
        latest_pending_affiliate: null,
    } }), null);
    assert.equal(ValidateAffiliatePendingApprovalSummary({ data: {
        pending_count: 0,
        latest_event_cursor: null,
        latest_event_status: null,
        latest_pending_affiliate: { id: 1, name: 'Unexpected', pending_at: 'now' },
    } }), null);
});

test('PollsTheAuthorizedSummaryAndUpdatesTheBackendAlert', async () => {
    const root = new FakeRoot();
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-pending-approval-badge]' ? [badge] : [],
    };
    const requests = [];
    const storage_values = new Map();
    const alert_client = CreateAffiliatePendingApprovalAlert({
        RootElement: root,
        DocumentObject: document_object,
        Storage: {
            getItem: (key) => storage_values.get(key) ?? null,
            setItem: (key, value) => storage_values.set(key, value),
        },
        FetchImplementation: async (url, options) => {
            requests.push({ url, options });
            return {
                ok: true,
                status: 200,
                json: async () => ({ data: {
                    pending_count: 1,
                    latest_event_cursor: '2026-08-27T04:00:00+00:00|2',
                    latest_event_status: 'PENDING_APPROVAL',
                    latest_pending_affiliate: {
                        id: 2,
                        name: 'New Affiliate',
                        pending_at: '2026-08-27T04:00:00+00:00',
                    },
                } }),
            };
        },
        IsVisible: () => true,
        ScheduleTimeout: () => 1,
        ClearScheduledTimeout: () => {},
        AddVisibilityListener: () => {},
        RemoveVisibilityListener: () => {},
    });

    await alert_client.poller.Poll();

    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, '/admin/affiliates/pending-approval-summary');
    assert.equal(requests[0].options.headers.Accept, 'application/json');
    assert.equal(badge.textContent, '1');
    assert.equal(root.querySelector('[data-affiliate-pending-approval-toast]').hidden, false);
    assert.equal(storage_values.get('affiliate-alert:1'), '2026-08-27T04:00:00+00:00|2');
});
