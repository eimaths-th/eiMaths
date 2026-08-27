import {
    CreateBookShipmentPoller,
    FormatBadgeCount,
} from './book-shipment-alert-poller.js';
import { GetPendingWorkAlertManager } from './pending-work-alert.js';

const DERIVED_STATUS_PRESENTATION = {
    DRAFT: { status_class: 'draft', label: 'แบบร่าง' },
    WAITING_FOR_CENTRE: { status_class: 'waiting', label: 'รอศูนย์รับหนังสือ' },
    PENDING_STUDENT_DELIVERY: { status_class: 'pending', label: 'รอส่งต่อให้นักเรียน' },
    COMPLETED: { status_class: 'complete', label: 'เสร็จสิ้น' },
    COMPLETED_WITH_ISSUES: { status_class: 'issue', label: 'เสร็จสิ้นแต่มีปัญหา' },
    CANCELLED: { status_class: 'cancelled', label: 'ยกเลิก' },
};

const STORED_STATUS_LABELS = {
    DRAFT: 'แบบร่าง',
    DISPATCHED: 'จัดส่งแล้ว',
    CENTRE_RECEIVED: 'ศูนย์รับแล้ว',
    CANCELLED: 'ยกเลิก',
};

const MONTH_LABELS = [
    '01', '02', '03', '04', '05', '06',
    '07', '08', '09', '10', '11', '12',
];

function IsPositiveInteger(value) {
    return Number.isSafeInteger(value) && value > 0;
}

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

function IsValidCursor(value) {
    return value === null || IsNonEmptyString(value);
}

function ValidateNotification(notification) {
    if (
        !notification
        || !IsNonEmptyString(notification.id)
        || !IsPositiveInteger(notification.shipment_id)
        || !IsNonEmptyString(notification.shipment_number)
        || !IsNonEmptyString(notification.received_at)
        || !IsNonEmptyString(notification.read_url)
    ) {
        return null;
    }

    return {
        id: notification.id,
        shipment_id: notification.shipment_id,
        shipment_number: notification.shipment_number,
        received_at: notification.received_at,
        read_url: notification.read_url,
    };
}

export function ValidateHqSummary(summary) {
    const summary_data = summary?.data;
    if (
        !summary_data
        || !Number.isSafeInteger(summary_data.active_shipment_count)
        || summary_data.active_shipment_count < 0
        || !Number.isSafeInteger(summary_data.unread_count)
        || summary_data.unread_count < 0
        || !IsValidCursor(summary_data.latest_event_cursor)
    ) {
        return null;
    }

    const latest_unread_notification = summary_data.latest_unread_notification === null
        ? null
        : ValidateNotification(summary_data.latest_unread_notification);
    if (
        (summary_data.latest_unread_notification !== null && latest_unread_notification === null)
        || (summary_data.unread_count > 0 && latest_unread_notification === null)
        || (summary_data.unread_count === 0 && latest_unread_notification !== null)
        || (summary_data.unread_count > 0 && summary_data.latest_event_cursor === null)
    ) {
        return null;
    }

    return {
        active_shipment_count: summary_data.active_shipment_count,
        unread_count: summary_data.unread_count,
        latest_event_cursor: summary_data.latest_event_cursor,
        latest_unread_notification,
    };
}

export function CreateHqSummaryHandler(dependencies) {
    let applied_ledger_cursor = null;

    return async (summary) => {
        const validated_summary = ValidateHqSummary(summary);
        if (!validated_summary) {
            throw new Error('Invalid HQ receipt alert summary');
        }

        dependencies.UpdateBadgeCount(validated_summary.active_shipment_count);

        let stored_toast_cursor = null;
        try {
            stored_toast_cursor = dependencies.ReadToastCursor();
        } catch (error) {
            stored_toast_cursor = null;
        }

        const latest_event_cursor = validated_summary.latest_event_cursor;
        const has_new_event = latest_event_cursor !== null && stored_toast_cursor !== latest_event_cursor;
        const pending_work_count = Math.max(
            validated_summary.active_shipment_count,
            validated_summary.unread_count,
        );
        if (dependencies.ToastManager) {
            const latest_unread_notification = validated_summary.latest_unread_notification;
            dependencies.ToastManager.HandleWorkItem({
                source_id: 'book',
                pending_count: pending_work_count,
                event_type: has_new_event && latest_unread_notification !== null ? 'centre_received' : null,
                title: 'รายการหนังสือ',
                reminder_message: `มีรายการจัดส่งหนังสือที่กำลังดำเนินการ ${pending_work_count} รายการ`,
                new_message: latest_unread_notification === null
                    ? 'มีรายการหนังสือใหม่ที่ต้องตรวจสอบ'
                    : `ศูนย์ยืนยันการรับรายการจัดส่ง ${latest_unread_notification.shipment_number} แล้ว`,
                action_url: dependencies.IndexUrl,
                action_label: 'ดูรายการหนังสือ',
            });
        } else if (has_new_event && validated_summary.latest_unread_notification !== null) {
            dependencies.ShowToast({
                event_type: 'centre_received',
                notification: validated_summary.latest_unread_notification,
            });
        }

        if (has_new_event) {
            try {
                dependencies.WriteToastCursor(latest_event_cursor);
            } catch (error) {
                // Storage is an enhancement; a transient storage failure must not stop polling.
            }
        }

        if (
            latest_event_cursor !== null
            && applied_ledger_cursor !== latest_event_cursor
            && dependencies.IsLedgerOpen()
        ) {
            try {
                await dependencies.RefreshLedger();
                applied_ledger_cursor = latest_event_cursor;
            } catch (error) {
                // Keep the cursor unapplied so the next poll retries without duplicating the toast.
            }
        }
    };
}

function NormalizeCount(value) {
    return Number.isSafeInteger(value) && value >= 0 ? value : 0;
}

function FormatCreatedLabel(created_at) {
    if (!IsNonEmptyString(created_at)) {
        return '—';
    }

    const match = created_at.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/);
    if (!match) {
        return '—';
    }

    const month_number = Number(match[2]);
    if (month_number < 1 || month_number > 12) {
        return '—';
    }

    return `${match[3]}/${MONTH_LABELS[month_number - 1]}/${match[1]} ${match[4]}:${match[5]}`;
}

export function BuildHqLedgerRowModel(shipment, detail_base_url) {
    if (
        !shipment
        || !IsPositiveInteger(shipment.id)
        || !IsNonEmptyString(shipment.shipment_number)
        || !IsNonEmptyString(shipment.status)
        || !IsNonEmptyString(shipment.derived_status)
        || !IsNonEmptyString(detail_base_url)
    ) {
        return null;
    }

    const status_presentation = DERIVED_STATUS_PRESENTATION[shipment.derived_status]
        ?? { status_class: 'draft', label: shipment.derived_status };
    const progress = shipment.progress ?? {};

    return {
        id: shipment.id,
        shipment_number: shipment.shipment_number,
        detail_url: `${String(detail_base_url).replace(/\/$/, '')}/${encodeURIComponent(String(shipment.id))}`,
        destination_centre_code: IsNonEmptyString(shipment.destination_centre_code)
            ? shipment.destination_centre_code
            : '—',
        destination_centre_name: IsNonEmptyString(shipment.destination_centre_name)
            ? shipment.destination_centre_name
            : 'ไม่พบข้อมูลศูนย์',
        status_class: status_presentation.status_class,
        derived_status_label: status_presentation.label,
        stored_status: shipment.status,
        total_count: NormalizeCount(progress.total_count),
        centre_received_count: NormalizeCount(progress.centre_received_count),
        student_received_count: NormalizeCount(progress.student_received_count),
        missing_count: NormalizeCount(progress.missing_count),
        damaged_count: NormalizeCount(progress.damaged_count),
        created_label: FormatCreatedLabel(shipment.created_at),
    };
}

function CreateJsonRequestHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

function CreateElement(document_object, tag_name, text_content = '', class_name = '') {
    const element = document_object.createElement(tag_name);
    if (class_name !== '') {
        element.className = class_name;
    }
    element.textContent = text_content;
    return element;
}

function BuildLedgerRow(document_object, row_model) {
    const row_element = document_object.createElement('tr');

    const shipment_cell = document_object.createElement('td');
    const shipment_link = CreateElement(document_object, 'a', row_model.shipment_number, 'book-shipment-number');
    shipment_link.href = row_model.detail_url;
    shipment_cell.appendChild(shipment_link);

    const destination_cell = document_object.createElement('td');
    destination_cell.appendChild(CreateElement(document_object, 'strong', row_model.destination_centre_code));
    destination_cell.appendChild(CreateElement(document_object, 'div', row_model.destination_centre_name, 'small text-muted'));

    const status_cell = document_object.createElement('td');
    status_cell.appendChild(CreateElement(
        document_object,
        'span',
        row_model.derived_status_label,
        `book-shipment-status book-shipment-status--${row_model.status_class}`,
    ));
    status_cell.appendChild(CreateElement(
        document_object,
        'div',
        `สถานะในระบบ: ${STORED_STATUS_LABELS[row_model.stored_status] ?? row_model.stored_status}`,
        'small text-muted mt-1',
    ));

    const items_cell = document_object.createElement('td');
    items_cell.appendChild(CreateElement(document_object, 'strong', String(row_model.total_count)));
    items_cell.appendChild(CreateElement(
        document_object,
        'div',
        `${row_model.centre_received_count} ศูนย์รับแล้ว`,
        'small text-muted',
    ));

    const delivery_cell = document_object.createElement('td');
    delivery_cell.appendChild(CreateElement(
        document_object,
        'strong',
        `${row_model.student_received_count} / ${row_model.centre_received_count}`,
    ));
    delivery_cell.appendChild(CreateElement(
        document_object,
        'div',
        `${row_model.missing_count} สูญหาย · ${row_model.damaged_count} ชำรุด`,
        'small text-muted',
    ));

    const created_cell = CreateElement(document_object, 'td', row_model.created_label, 'small text-muted');

    const action_cell = document_object.createElement('td');
    action_cell.className = 'text-right';
    const action_link = CreateElement(document_object, 'a', 'ดูรายละเอียด', 'btn btn-sm btn-outline-secondary');
    action_link.href = row_model.detail_url;
    action_cell.appendChild(action_link);

    row_element.appendChild(shipment_cell);
    row_element.appendChild(destination_cell);
    row_element.appendChild(status_cell);
    row_element.appendChild(items_cell);
    row_element.appendChild(delivery_cell);
    row_element.appendChild(created_cell);
    row_element.appendChild(action_cell);

    return row_element;
}

function BuildLedgerPageUrl(document_object, current_url, page_number) {
    const base_url = document_object.baseURI || 'http://localhost/';
    const page_url = new URL(current_url, base_url);
    page_url.searchParams.set('page', String(page_number));
    return page_url.toString();
}

function RenderLedgerPagination(document_object, pagination_element, current_url, meta) {
    pagination_element.replaceChildren();
    if (meta.last_page <= 1) {
        return;
    }

    const navigation_element = document_object.createElement('nav');
    navigation_element.setAttribute('aria-label', 'หน้ารายการจัดส่งหนังสือ');
    const list_element = document_object.createElement('ul');
    list_element.className = 'pagination mb-0';

    for (let page_number = 1; page_number <= meta.last_page; page_number += 1) {
        const list_item = document_object.createElement('li');
        list_item.className = `page-item${page_number === meta.current_page ? ' active' : ''}`;
        const page_link = CreateElement(document_object, 'a', String(page_number), 'page-link');
        page_link.href = BuildLedgerPageUrl(document_object, current_url, page_number);
        if (page_number === meta.current_page) {
            page_link.setAttribute('aria-current', 'page');
        }
        list_item.appendChild(page_link);
        list_element.appendChild(list_item);
    }

    navigation_element.appendChild(list_element);
    pagination_element.appendChild(navigation_element);
}

export function RenderHqLedger(document_object, ledger_response, current_url, detail_base_url) {
    const ledger_body = document_object.querySelector('[data-book-shipment-ledger-body]');
    const pagination_element = document_object.querySelector('[data-book-shipment-ledger-pagination]');
    const ledger_data = ledger_response?.data;
    const meta = ledger_response?.meta;
    if (
        !ledger_body
        || !pagination_element
        || !Array.isArray(ledger_data)
        || !meta
        || !Number.isSafeInteger(meta.current_page)
        || !Number.isSafeInteger(meta.last_page)
        || meta.current_page < 1
        || meta.last_page < 1
    ) {
        throw new Error('Invalid HQ ledger response');
    }

    const row_models = ledger_data.map((shipment) => BuildHqLedgerRowModel(shipment, detail_base_url));
    if (row_models.some((row_model) => row_model === null)) {
        throw new Error('Invalid HQ ledger shipment row');
    }

    const document_fragment = document_object.createDocumentFragment();
    if (row_models.length === 0) {
        const empty_row = document_object.createElement('tr');
        const empty_cell = CreateElement(
            document_object,
            'td',
            'ไม่พบรายการจัดส่งตามเงื่อนไขนี้',
            'book-shipment-empty',
        );
        empty_cell.colSpan = 7;
        empty_row.appendChild(empty_cell);
        document_fragment.appendChild(empty_row);
    } else {
        row_models.forEach((row_model) => {
            document_fragment.appendChild(BuildLedgerRow(document_object, row_model));
        });
    }

    ledger_body.replaceChildren(document_fragment);
    RenderLedgerPagination(document_object, pagination_element, current_url, meta);
}

async function FetchAndRenderHqLedger(
    fetch_implementation,
    document_object,
    current_url,
    detail_base_url,
) {
    const response = await fetch_implementation(current_url, {
        headers: CreateJsonRequestHeaders(),
    });
    if (!response.ok) {
        const error = new Error(`HQ ledger request failed with status ${response.status}`);
        error.status = response.status;
        throw error;
    }

    const ledger_response = await response.json();
    RenderHqLedger(document_object, ledger_response, current_url, detail_base_url);
}

function RenderHqBadgeCount(document_object, active_shipment_count) {
    const badge_text = FormatBadgeCount(active_shipment_count);
    document_object.querySelectorAll('[data-book-shipment-badge="hq"]').forEach((badge_element) => {
        badge_element.textContent = badge_text;
        badge_element.hidden = badge_text === '';
        badge_element.setAttribute(
            'aria-label',
            badge_text === ''
                ? 'ไม่มีรายการจัดส่งหนังสือที่กำลังดำเนินการ'
                : `มีรายการจัดส่งหนังสือที่กำลังดำเนินการ ${active_shipment_count} รายการ`,
        );
    });
}

function RenderHqToast(root_element, toast_event) {
    const toast_element = root_element.querySelector('[data-book-shipment-alert-toast]');
    const title_element = root_element.querySelector('[data-book-shipment-alert-title]');
    const message_element = root_element.querySelector('[data-book-shipment-alert-message]');
    const form_element = root_element.querySelector('[data-book-shipment-alert-read-form]');
    const action_element = root_element.querySelector('[data-book-shipment-alert-hq-action]');
    if (!toast_element || !title_element || !message_element || !form_element || !action_element) {
        return;
    }

    const notification = toast_event.notification;
    title_element.textContent = 'ศูนย์รับหนังสือแล้ว';
    message_element.textContent = `ศูนย์ยืนยันการรับรายการจัดส่ง ${notification.shipment_number} แล้ว`;
    form_element.action = notification.read_url;
    form_element.hidden = false;
    action_element.disabled = false;
    toast_element.hidden = false;
}

export function CreateHqCentreReceiptAlert(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const summary_url = root_element?.getAttribute('data-summary-url');
    const index_url = root_element?.getAttribute('data-index-url');
    const detail_base_url = root_element?.getAttribute('data-detail-base-url');
    const storage_key = root_element?.getAttribute('data-storage-key');

    if (!root_element || !document_object || !summary_url || !index_url || !detail_base_url || !storage_key) {
        return null;
    }

    const current_ledger_url = () => dependencies.CurrentLedgerUrl();
    const summary_handler = CreateHqSummaryHandler({
        ReadToastCursor: () => dependencies.Storage.getItem(storage_key),
        WriteToastCursor: (next_cursor) => dependencies.Storage.setItem(storage_key, next_cursor),
        UpdateBadgeCount: (active_shipment_count) => RenderHqBadgeCount(document_object, active_shipment_count),
        IndexUrl: index_url,
        ToastManager: dependencies.ToastManager,
        ShowToast: (toast_event) => RenderHqToast(root_element, toast_event),
        IsLedgerOpen: dependencies.IsLedgerOpen,
        RefreshLedger: () => FetchAndRenderHqLedger(
            dependencies.FetchImplementation,
            document_object,
            current_ledger_url(),
            detail_base_url,
        ),
    });

    const dismiss_element = root_element.querySelector('[data-book-shipment-alert-dismiss]');
    const toast_element = root_element.querySelector('[data-book-shipment-alert-toast]');
    dismiss_element?.addEventListener('click', () => {
        if (toast_element) {
            toast_element.hidden = true;
        }
    });

    const poller = CreateBookShipmentPoller({
        FetchSummary: async () => {
            const response = await dependencies.FetchImplementation(summary_url, {
                headers: CreateJsonRequestHeaders(),
            });
            if (!response.ok) {
                const error = new Error(`HQ summary request failed with status ${response.status}`);
                error.status = response.status;
                throw error;
            }
            return response.json();
        },
        HandleSummary: summary_handler,
        IsVisible: dependencies.IsVisible,
        ScheduleTimeout: dependencies.ScheduleTimeout,
        ClearScheduledTimeout: dependencies.ClearScheduledTimeout,
        AddVisibilityListener: dependencies.AddVisibilityListener,
        RemoveVisibilityListener: dependencies.RemoveVisibilityListener,
    });

    return { poller };
}

export function BootstrapHqCentreReceiptAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    FetchImplementation = globalThis.fetch?.bind(globalThis),
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !WindowObject || !FetchImplementation || !Storage) {
        return null;
    }

    const root_element = DocumentObject.querySelector('[data-book-shipment-alert-role="hq"]');
    if (!root_element) {
        return null;
    }

    const alert_client = CreateHqCentreReceiptAlert({
        RootElement: root_element,
        DocumentObject,
        Storage,
        FetchImplementation,
        ToastManager: GetPendingWorkAlertManager(),
        CurrentLedgerUrl: () => `${WindowObject.location.pathname}${WindowObject.location.search}`,
        IsLedgerOpen: () => DocumentObject.querySelector('[data-book-shipment-ledger]') !== null,
        IsVisible: () => DocumentObject.visibilityState !== 'hidden',
        ScheduleTimeout: (callback, delay_ms) => WindowObject.setTimeout(callback, delay_ms),
        ClearScheduledTimeout: (timer_id) => WindowObject.clearTimeout(timer_id),
        AddVisibilityListener: (listener) => DocumentObject.addEventListener('visibilitychange', listener),
        RemoveVisibilityListener: (listener) => DocumentObject.removeEventListener('visibilitychange', listener),
    });

    alert_client?.poller.Start();
    return alert_client;
}

if (typeof globalThis.document !== 'undefined') {
    BootstrapHqCentreReceiptAlert();
}
