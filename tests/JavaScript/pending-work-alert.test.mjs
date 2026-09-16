import test from 'node:test';
import assert from 'node:assert/strict';
import { CreatePendingWorkAlertManager } from '../../public/js/pending-work-alert.js';

class FakeNode {
    constructor(children = {}) {
        this.children = children;
        this.textContent = '';
        this.hidden = true;
        this.href = '';
        this.attributes = {};
        this.listeners = {};
    }

    querySelector(selector) {
        return this.children[selector] ?? null;
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    addEventListener(event_name, listener) {
        this.listeners[event_name] = listener;
    }

    Click(event_name = 'click') {
        this.listeners[event_name]?.();
    }
}

class FakeRoot extends FakeNode {
    constructor() {
        const notification_toggle = new FakeNode();
        notification_toggle.hidden = false;
        const notification_badge = new FakeNode();
        const notification_total = new FakeNode();
        const notification_panel = new FakeNode();
        const empty_state = new FakeNode();
        const affiliate_item = new FakeNode({
            '[data-pending-work-item-title]': new FakeNode(),
            '[data-pending-work-item-count]': new FakeNode(),
            '[data-pending-work-item-message]': new FakeNode(),
            '[data-pending-work-item-action]': new FakeNode(),
        });
        const course_application_item = new FakeNode({
            '[data-pending-work-item-title]': new FakeNode(),
            '[data-pending-work-item-count]': new FakeNode(),
            '[data-pending-work-item-message]': new FakeNode(),
            '[data-pending-work-item-action]': new FakeNode(),
        });
        const book_item = new FakeNode({
            '[data-pending-work-item-title]': new FakeNode(),
            '[data-pending-work-item-count]': new FakeNode(),
            '[data-pending-work-item-message]': new FakeNode(),
            '[data-pending-work-item-action]': new FakeNode(),
        });

        super({
            '[data-pending-work-alert-toast]': new FakeNode(),
            '[data-pending-work-alert-dismiss]': new FakeNode(),
            '[data-pending-work-alert-toggle]': notification_toggle,
            '[data-pending-work-alert-badge]': notification_badge,
            '[data-pending-work-alert-total]': notification_total,
            '[data-pending-work-alert-panel]': notification_panel,
            '[data-pending-work-alert-empty]': empty_state,
            '[data-pending-work-item="affiliate"]': affiliate_item,
            '[data-pending-work-item="course_application"]': course_application_item,
            '[data-pending-work-item="book"]': book_item,
        });
        this.attributes = { 'data-storage-key': 'pending-work-alert:1' };
        this.hidden = false;
        this.notification_toggle = notification_toggle;
        this.notification_badge = notification_badge;
        this.notification_total = notification_total;
        this.notification_panel = notification_panel;
        this.empty_state = empty_state;
        this.affiliate_item = affiliate_item;
        this.course_application_item = course_application_item;
        this.book_item = book_item;
    }

    getAttribute(name) {
        return this.attributes[name] ?? null;
    }

    querySelectorAll(selector) {
        return selector === '[data-pending-work-item]'
            ? [this.affiliate_item, this.course_application_item, this.book_item]
            : [];
    }
}

function CreateStorage() {
    const values = new Map();

    return {
        values,
        getItem: (key) => values.get(key) ?? null,
        setItem: (key, value) => values.set(key, String(value)),
    };
}

function CreateWorkItem(source_id, pending_count, event_type = null) {
    const is_affiliate = source_id === 'affiliate';
    const is_course_application = source_id === 'course_application';

    return {
        source_id,
        pending_count,
        event_type,
        title: is_affiliate
            ? 'Affiliate account'
            : is_course_application
                ? 'Affiliate Course Applications'
                : 'รายการหนังสือ',
        reminder_message: is_affiliate
            ? `มี Affiliate รออนุมัติ ${pending_count} รายการ`
            : is_course_application
                ? `มีใบสมัครคอร์สรอดำเนินการ ${pending_count} รายการ`
                : `มีรายการหนังสือที่กำลังดำเนินการ ${pending_count} รายการ`,
        new_message: is_affiliate
            ? 'New Affiliate สมัคร Affiliate และกำลังรออนุมัติ (1 รายการ)'
            : is_course_application
                ? 'New Parent สมัครคอร์ส Math Starter ที่สาขา Ratchapruek และกำลังรอดำเนินการ (1 รายการ)'
                : 'Shipment-001 มีหนังสือ 4 เล่มรอตรวจรับ',
        action_url: is_affiliate
            ? '/admin/affiliates'
            : is_course_application
                ? '/courses/pending/index'
                : '/incoming-book-shipments',
        action_label: is_affiliate
            ? 'ดูรายการ Affiliate ที่รออนุมัติ'
            : is_course_application
                ? 'ดูใบสมัครคอร์ส'
                : 'ดูรายการหนังสือที่กำลังจัดส่งเข้า',
    };
}

test('ShowsPendingWorkInNavbarBadgeWithoutOpeningNotificationPanel', () => {
    const root = new FakeRoot();
    const storage = CreateStorage();
    const alert_manager = CreatePendingWorkAlertManager({
        RootElement: root,
        Storage: storage,
    });

    alert_manager.HandleWorkItem(CreateWorkItem('affiliate', 1, 'pending_summary'));

    assert.equal(alert_manager.IsPanelOpen(), false);
    assert.equal(root.notification_panel.hidden, true);
    assert.equal(root.notification_toggle.attributes['aria-expanded'], 'false');
    assert.equal(root.notification_badge.hidden, false);
    assert.equal(root.notification_badge.textContent, '1');
    assert.equal(root.notification_total.textContent, '1 รายการ');
    assert.equal(root.affiliate_item.hidden, false);
    assert.equal(root.affiliate_item.querySelector('[data-pending-work-item-count]').textContent, '1');
});

test('OpensAndClosesCombinedPendingWorkFromNavbarNotificationButton', () => {
    const root = new FakeRoot();
    const storage = CreateStorage();
    const alert_manager = CreatePendingWorkAlertManager({
        RootElement: root,
        Storage: storage,
    });

    alert_manager.HandleWorkItem(CreateWorkItem('affiliate', 1, 'pending_summary'));
    alert_manager.HandleWorkItem(CreateWorkItem('book', 2));

    root.notification_toggle.Click();
    assert.equal(alert_manager.IsPanelOpen(), true);
    assert.equal(root.notification_panel.hidden, false);
    assert.equal(root.notification_toggle.attributes['aria-expanded'], 'true');
    assert.equal(root.notification_badge.textContent, '3');
    assert.equal(root.notification_total.textContent, '3 รายการ');
    assert.equal(root.affiliate_item.hidden, false);
    assert.equal(root.book_item.hidden, false);

    root.querySelector('[data-pending-work-alert-dismiss]').Click();
    assert.equal(alert_manager.IsPanelOpen(), false);
    assert.equal(root.notification_panel.hidden, true);
    assert.equal(root.notification_toggle.attributes['aria-expanded'], 'false');
    assert.equal(root.notification_badge.hidden, false);
});

test('ShowsEmptyStateAndClearsBadgeWhenAllPendingWorkIsResolved', () => {
    const root = new FakeRoot();
    const storage = CreateStorage();
    const alert_manager = CreatePendingWorkAlertManager({
        RootElement: root,
        Storage: storage,
    });

    alert_manager.HandleWorkItem(CreateWorkItem('affiliate', 1, 'pending_summary'));
    root.notification_toggle.Click();

    alert_manager.HandleWorkItem(CreateWorkItem('affiliate', 0));
    alert_manager.HandleWorkItem(CreateWorkItem('book', 0));

    assert.equal(root.notification_panel.hidden, false);
    assert.equal(root.notification_badge.hidden, true);
    assert.equal(root.notification_total.textContent, 'ไม่มีงานค้าง');
    assert.equal(root.empty_state.hidden, false);
    assert.equal(root.affiliate_item.hidden, true);
    assert.equal(root.book_item.hidden, true);
});

test('CombinesAffiliateAccountsAndCourseApplicationsInTheAffiliateManagementBadge', () => {
    const root = new FakeRoot();
    const affiliate_management_badge = new FakeNode();
    const document_object = {
        querySelectorAll: (selector) => selector === '[data-affiliate-management-pending-badge]'
            ? [affiliate_management_badge]
            : [],
    };
    const alert_manager = CreatePendingWorkAlertManager({
        RootElement: root,
        DocumentObject: document_object,
        Storage: CreateStorage(),
    });

    alert_manager.HandleWorkItem(CreateWorkItem('affiliate', 2));
    alert_manager.HandleWorkItem(CreateWorkItem('course_application', 3, 'new_application'));
    alert_manager.HandleWorkItem(CreateWorkItem('book', 4));

    assert.equal(root.notification_badge.textContent, '9');
    assert.equal(affiliate_management_badge.textContent, '5');
    assert.equal(affiliate_management_badge.hidden, false);
    assert.equal(root.course_application_item.hidden, false);
    assert.equal(
        root.course_application_item.querySelector('[data-pending-work-item-message]').textContent,
        'New Parent สมัครคอร์ส Math Starter ที่สาขา Ratchapruek และกำลังรอดำเนินการ (1 รายการ)',
    );
});
