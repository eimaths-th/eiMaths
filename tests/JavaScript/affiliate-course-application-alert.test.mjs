import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CreateAffiliateCourseApplicationAlert,
    CreateAffiliateCourseApplicationSummaryHandler,
    RenderAffiliateCourseApplicationBadgeCount,
    ValidateAffiliateCourseApplicationSummary,
} from '../../public/js/affiliate-course-application-alert.js';

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
            'data-summary-url': '/admin/affiliate-course-applications/pending-summary',
            'data-index-url': '/courses/pending/index',
            'data-storage-key': 'affiliate-course-application-alert:1',
        };
    }

    getAttribute(name) {
        return this.attributes[name] ?? null;
    }
}

function CreateSummary(overrides = {}) {
    return {
        data: {
            pending_count: 1,
            latest_event_cursor: '2026-09-16T04:00:00+00:00|2',
            latest_event_status: '1',
            latest_pending_application: {
                id: 2,
                applicant_name: 'New Parent',
                course_name: 'Math Starter',
                branch_name: 'Ratchapruek',
                submitted_at: '2026-09-16T04:00:00+00:00',
            },
            ...overrides,
        },
    };
}

test('ValidatesAndHandlesANewPendingCourseApplication', async () => {
    const summary = CreateSummary();
    const handled_items = [];
    let stored_cursor = '2026-09-16T03:00:00+00:00|1';
    const handler = CreateAffiliateCourseApplicationSummaryHandler({
        ReadCursor: () => stored_cursor,
        WriteCursor: (cursor) => { stored_cursor = cursor; },
        UpdateBadgeCount: () => {},
        IndexUrl: '/courses/pending/index',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
    });

    assert.deepEqual(ValidateAffiliateCourseApplicationSummary(summary), summary.data);
    await handler(summary);

    assert.equal(handled_items.length, 1);
    assert.equal(handled_items[0].source_id, 'course_application');
    assert.equal(handled_items[0].event_type, 'new_application');
    assert.match(handled_items[0].new_message, /New Parent.*Math Starter/);
    assert.equal(stored_cursor, '2026-09-16T04:00:00+00:00|2');
});

test('UpdatesCourseApplicationMenuBadges', () => {
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-course-application-badge]'
            ? [badge]
            : [],
    };

    RenderAffiliateCourseApplicationBadgeCount(document_object, 3);
    assert.equal(badge.textContent, '3');
    assert.equal(badge.hidden, false);

    RenderAffiliateCourseApplicationBadgeCount(document_object, 0);
    assert.equal(badge.textContent, '');
    assert.equal(badge.hidden, true);
});

test('RejectsMalformedCourseApplicationSummaries', () => {
    assert.equal(ValidateAffiliateCourseApplicationSummary(CreateSummary({
        pending_count: 1,
        latest_event_cursor: null,
    })), null);
    assert.equal(ValidateAffiliateCourseApplicationSummary(CreateSummary({
        pending_count: 0,
    })), null);
});

test('PollsTheAuthorizedCourseApplicationSummary', async () => {
    const root = new FakeRoot();
    const badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-course-application-badge]'
            ? [badge]
            : [],
    };
    const handled_items = [];
    const requests = [];
    const storage_values = new Map();
    const alert_client = CreateAffiliateCourseApplicationAlert({
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
    assert.equal(requests[0].url, '/admin/affiliate-course-applications/pending-summary');
    assert.equal(requests[0].options.headers.Accept, 'application/json');
    assert.equal(badge.textContent, '1');
    assert.equal(handled_items[0].source_id, 'course_application');
    assert.equal(
        storage_values.get('affiliate-course-application-alert:1'),
        '2026-09-16T04:00:00+00:00|2',
    );
});
