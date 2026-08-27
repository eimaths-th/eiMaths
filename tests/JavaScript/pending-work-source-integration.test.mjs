import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CreateAffiliatePendingApprovalSummaryHandler,
} from '../../public/js/affiliate-pending-approval-alert.js';
import { CreateCentreSummaryHandler } from '../../public/js/centre-incoming-shipment-alert.js';
import {
    CreateHqCentreReceiptAlert,
    CreateHqSummaryHandler,
} from '../../public/js/hq-centre-receipt-alert.js';

test('SendsOutstandingAffiliateWorkToThePendingWorkManager', async () => {
    const handled_items = [];
    const handler = CreateAffiliatePendingApprovalSummaryHandler({
        ReadCursor: () => '2026-08-27T04:00:00+00:00|2',
        WriteCursor: () => {},
        UpdateBadgeCount: () => {},
        IndexUrl: '/admin/affiliates',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
        ShowToast: () => assert.fail('The source toast should be delegated to the shared manager'),
    });

    await handler({ data: {
        pending_count: 1,
        latest_event_cursor: '2026-08-27T04:00:00+00:00|2',
        latest_event_status: 'PENDING_APPROVAL',
        latest_pending_affiliate: {
            id: 2,
            name: 'New Affiliate',
            pending_at: '2026-08-27T04:00:00+00:00',
        },
    } });

    assert.deepEqual(handled_items, [{
        source_id: 'affiliate',
        pending_count: 1,
        event_type: null,
        title: 'Affiliate account',
        reminder_message: 'มี Affiliate รออนุมัติ 1 รายการ',
        new_message: 'New Affiliate สมัคร Affiliate และกำลังรออนุมัติ (1 รายการ)',
        action_url: '/admin/affiliates',
        action_label: 'ดูรายการ Affiliate ที่รออนุมัติ',
    }]);
});

test('SendsOutstandingCentreBookWorkToThePendingWorkManager', async () => {
    const handled_items = [];
    const handler = CreateCentreSummaryHandler({
        ReadCursor: () => '2026-08-27T04:00:00+00:00|2',
        WriteCursor: () => {},
        UpdateBadgeCount: () => {},
        IndexUrl: '/incoming-book-shipments',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
        ShowToast: () => assert.fail('The source toast should be delegated to the shared manager'),
    });

    await handler({ data: {
        pending_count: 1,
        latest_shipment: {
            id: 2,
            shipment_number: 'Shipment-001',
            item_count: 4,
            dispatched_at: '2026-08-27T04:00:00+00:00',
        },
    } });

    assert.deepEqual(handled_items, [{
        source_id: 'book',
        pending_count: 1,
        event_type: null,
        title: 'รายการหนังสือ',
        reminder_message: 'มีรายการจัดส่งหนังสือจากศูนย์จัดส่ง 1 รายการรอตรวจรับ',
        new_message: 'Shipment-001 มีหนังสือ 4 เล่มรอตรวจรับ',
        action_url: '/incoming-book-shipments',
        action_label: 'ดูรายการหนังสือที่กำลังจัดส่งเข้า',
    }]);
});

test('SendsOutstandingHqBookWorkToThePendingWorkManager', async () => {
    const handled_items = [];
    const handler = CreateHqSummaryHandler({
        ReadToastCursor: () => '2026-08-27T04:00:00+00:00|notification-1',
        WriteToastCursor: () => {},
        UpdateBadgeCount: () => {},
        IndexUrl: '/book-shipments',
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
        ShowToast: () => assert.fail('The source toast should be delegated to the shared manager'),
        IsLedgerOpen: () => false,
        RefreshLedger: async () => {},
    });

    await handler({ data: {
        active_shipment_count: 2,
        unread_count: 1,
        latest_event_cursor: '2026-08-27T04:00:00+00:00|notification-1',
        latest_unread_notification: {
            id: 'notification-1',
            shipment_id: 2,
            shipment_number: 'Shipment-001',
            received_at: '2026-08-27T04:00:00+00:00',
            read_url: '/book-shipments/notifications/notification-1/read',
        },
    } });

    assert.deepEqual(handled_items, [{
        source_id: 'book',
        pending_count: 2,
        event_type: null,
        title: 'รายการหนังสือ',
        reminder_message: 'มีรายการจัดส่งหนังสือที่กำลังดำเนินการ 2 รายการ',
        new_message: 'ศูนย์ยืนยันการรับรายการจัดส่ง Shipment-001 แล้ว',
        action_url: '/book-shipments',
        action_label: 'ดูรายการหนังสือ',
    }]);
});

test('UsesTheConfiguredHqBookListUrlForTheSharedPendingWorkAction', async () => {
    const handled_items = [];
    const root_element = {
        attributes: {
            'data-summary-url': '/book-shipments/receipt-alert-summary',
            'data-index-url': '/book-shipments',
            'data-detail-base-url': '/book-shipments',
            'data-storage-key': 'book-shipment-alert:hq:1',
        },
        getAttribute: (name) => root_element.attributes[name] ?? null,
        querySelector: () => null,
    };
    const client = CreateHqCentreReceiptAlert({
        RootElement: root_element,
        DocumentObject: { querySelectorAll: () => [] },
        Storage: {
            getItem: () => '2026-08-27T04:00:00+00:00|notification-1',
            setItem: () => {},
        },
        FetchImplementation: async () => ({
            ok: true,
            status: 200,
            json: async () => ({ data: {
                active_shipment_count: 2,
                unread_count: 1,
                latest_event_cursor: '2026-08-27T04:00:00+00:00|notification-1',
                latest_unread_notification: {
                    id: 'notification-1',
                    shipment_id: 2,
                    shipment_number: 'Shipment-001',
                    received_at: '2026-08-27T04:00:00+00:00',
                    read_url: '/book-shipments/notifications/notification-1/read',
                },
            } }),
        }),
        ToastManager: {
            HandleWorkItem: (work_item) => handled_items.push(work_item),
        },
        IsLedgerOpen: () => false,
        RefreshLedger: async () => {},
        IsVisible: () => true,
        ScheduleTimeout: () => 1,
        ClearScheduledTimeout: () => {},
        AddVisibilityListener: () => {},
        RemoveVisibilityListener: () => {},
    });

    await client.poller.Poll();

    assert.equal(handled_items.length, 1);
    assert.equal(handled_items[0].action_url, '/book-shipments');
});
