import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CreateAffiliateWithdrawalAlert,
    CreateAffiliateWithdrawalSummaryHandler,
    RenderAffiliateWithdrawalBadgeCount,
    ValidateAffiliateWithdrawalSummary,
} from '../../public/js/affiliate-withdrawal-alert.js';

class FakeNode {
    constructor() {
        this.textContent = '';
        this.hidden = true;
        this.attributes = {};
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }
}

class FakeRoot {
    constructor() {
        this.attributes = {
            'data-summary-url': '/admin/affiliate-withdrawals/pending-summary',
            'data-index-url': '/admin/affiliate-withdrawals',
            'data-storage-key': 'affiliate-withdrawal-alert:1',
        };
    }

    getAttribute(name) {
        return this.attributes[name] ?? null;
    }
}

function CreateSummary(overrides = {}) {
    return {
        data: {
            actionable_count: 1,
            requested_count: 1,
            approved_count: 0,
            latest_event_cursor: '2026-09-16T09:45:00+00:00|8',
            latest_event_status: 'REQUESTED',
            latest_actionable_withdrawal: {
                id: 8,
                affiliate_name: 'New Affiliate',
                amount_minor: 500000,
                status: 'REQUESTED',
                requested_at: '2026-09-16T09:45:00+00:00',
            },
            ...overrides,
        },
    };
}

test('ValidatesAndHandlesANewAffiliateWithdrawal', async () => {
    const summary = CreateSummary();
    const handled_items = [];
    let stored_cursor = '2026-09-16T09:30:00+00:00|7';
    const handler = CreateAffiliateWithdrawalSummaryHandler({
        ReadCursor: () => stored_cursor,
        WriteCursor: (cursor) => { stored_cursor = cursor; },
        UpdateBadgeCount: () => {},
        IndexUrl: '/admin/affiliate-withdrawals',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
    });

    assert.deepEqual(ValidateAffiliateWithdrawalSummary(summary), summary.data);
    await handler(summary);

    assert.equal(handled_items.length, 1);
    assert.equal(handled_items[0].source_id, 'withdrawal');
    assert.equal(handled_items[0].event_type, 'new_withdrawal');
    assert.match(handled_items[0].new_message, /New Affiliate.*5,000\.00/);
    assert.equal(stored_cursor, '2026-09-16T09:45:00+00:00|8');
});

test('UpdatesAffiliateWithdrawalMenuBadges', () => {
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-withdrawal-badge]'
            ? [badge]
            : [],
    };

    RenderAffiliateWithdrawalBadgeCount(document_object, 4);
    assert.equal(badge.textContent, '4');
    assert.equal(badge.hidden, false);

    RenderAffiliateWithdrawalBadgeCount(document_object, 0);
    assert.equal(badge.textContent, '');
    assert.equal(badge.hidden, true);
});

test('ClearsPendingWorkWhenNoAffiliateWithdrawalsRemain', async () => {
    const handled_items = [];
    const handler = CreateAffiliateWithdrawalSummaryHandler({
        ReadCursor: () => '2026-09-16T09:45:00+00:00|8',
        WriteCursor: () => {},
        UpdateBadgeCount: () => {},
        IndexUrl: '/admin/affiliate-withdrawals',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
    });

    await handler(CreateSummary({
        actionable_count: 0,
        requested_count: 0,
        approved_count: 0,
        latest_event_cursor: '2026-09-16T09:50:00+00:00|8',
        latest_event_status: 'PAID',
        latest_actionable_withdrawal: null,
    }));

    assert.equal(handled_items[0].pending_count, 0);
    assert.equal(handled_items[0].reminder_message, 'ไม่มีคำขอถอนเงินที่ต้องจัดการ');
});

test('RejectsMalformedAffiliateWithdrawalSummaries', () => {
    assert.equal(ValidateAffiliateWithdrawalSummary(CreateSummary({
        actionable_count: 2,
    })), null);
    assert.equal(ValidateAffiliateWithdrawalSummary(CreateSummary({
        actionable_count: 1,
        latest_actionable_withdrawal: null,
    })), null);
});

test('PollsTheAuthorizedAffiliateWithdrawalSummary', async () => {
    const root = new FakeRoot();
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-withdrawal-badge]'
            ? [badge]
            : [],
    };
    const handled_items = [];
    const requests = [];
    const storage_values = new Map();
    const alert_client = CreateAffiliateWithdrawalAlert({
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
                json: async () => CreateSummary(),
            };
        },
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
        IsVisible: () => true,
        ScheduleTimeout: () => 1,
        ClearScheduledTimeout: () => {},
        AddVisibilityListener: () => {},
        RemoveVisibilityListener: () => {},
    });

    await alert_client.poller.Poll();

    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, '/admin/affiliate-withdrawals/pending-summary');
    assert.equal(requests[0].options.headers.Accept, 'application/json');
    assert.equal(badge.textContent, '1');
    assert.equal(handled_items[0].source_id, 'withdrawal');
    assert.equal(
        storage_values.get('affiliate-withdrawal-alert:1'),
        '2026-09-16T09:45:00+00:00|8',
    );
});
