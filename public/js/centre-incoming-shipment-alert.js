import {
    BuildEventCursor,
    CreateBookShipmentPoller,
    FormatBadgeCount,
} from './book-shipment-alert-poller.js';
import { GetPendingWorkAlertManager } from './pending-work-alert.js';

function IsPositiveInteger(value) {
    return Number.isSafeInteger(value) && value > 0;
}

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

export function ValidateCentreSummary(summary) {
    const summary_data = summary?.data;
    if (!summary_data || !Number.isSafeInteger(summary_data.pending_count) || summary_data.pending_count < 0) {
        return null;
    }

    if (summary_data.pending_count === 0) {
        return summary_data.latest_shipment === null
            ? { pending_count: 0, latest_shipment: null }
            : null;
    }

    const latest_shipment = summary_data.latest_shipment;
    if (
        !latest_shipment
        || !IsPositiveInteger(latest_shipment.id)
        || !IsNonEmptyString(latest_shipment.shipment_number)
        || !Number.isSafeInteger(latest_shipment.item_count)
        || latest_shipment.item_count < 0
        || !IsNonEmptyString(latest_shipment.dispatched_at)
    ) {
        return null;
    }

    return {
        pending_count: summary_data.pending_count,
        latest_shipment: {
            id: latest_shipment.id,
            shipment_number: latest_shipment.shipment_number,
            item_count: latest_shipment.item_count,
            dispatched_at: latest_shipment.dispatched_at,
        },
    };
}

function HasStoredCursor(cursor) {
    return IsNonEmptyString(cursor);
}

export function CreateCentreSummaryHandler(dependencies) {
    return async (summary) => {
        const validated_summary = ValidateCentreSummary(summary);
        if (!validated_summary) {
            throw new Error('Invalid Centre book shipment summary');
        }

        dependencies.UpdateBadgeCount(validated_summary.pending_count);

        if (validated_summary.pending_count === 0) {
            if (dependencies.ToastManager) {
                dependencies.ToastManager.HandleWorkItem({
                    source_id: 'book',
                    pending_count: 0,
                    event_type: null,
                    title: 'รายการหนังสือ',
                    reminder_message: 'ไม่มีรายการหนังสือที่กำลังจัดส่งเข้า',
                    new_message: 'มีรายการหนังสือใหม่รอตรวจรับ',
                    action_url: dependencies.IndexUrl,
                    action_label: 'ดูรายการหนังสือที่กำลังจัดส่งเข้า',
                });
            }

            return;
        }

        let stored_cursor = null;
        try {
            stored_cursor = dependencies.ReadCursor();
        } catch (error) {
            stored_cursor = null;
        }

        const latest_shipment = validated_summary.latest_shipment;
        const latest_cursor = BuildEventCursor(latest_shipment.dispatched_at, latest_shipment.id);
        if (latest_cursor === null) {
            throw new Error('Invalid Centre book shipment cursor');
        }

        const event_type = stored_cursor !== latest_cursor
            ? HasStoredCursor(stored_cursor) ? 'new_shipment' : 'pending_summary'
            : null;
        if (dependencies.ToastManager) {
            dependencies.ToastManager.HandleWorkItem({
                source_id: 'book',
                pending_count: validated_summary.pending_count,
                event_type,
                title: 'รายการหนังสือ',
                reminder_message: `มีรายการจัดส่งหนังสือจากศูนย์จัดส่ง ${validated_summary.pending_count} รายการรอตรวจรับ`,
                new_message: `${latest_shipment.shipment_number} มีหนังสือ ${latest_shipment.item_count} เล่มรอตรวจรับ`,
                action_url: dependencies.IndexUrl,
                action_label: 'ดูรายการหนังสือที่กำลังจัดส่งเข้า',
            });
        } else if (event_type !== null) {
            dependencies.ShowToast({
                event_type,
                pending_count: validated_summary.pending_count,
                latest_shipment,
            });
        }

        try {
            dependencies.WriteCursor(latest_cursor);
        } catch (error) {
            // Storage is an enhancement; a transient storage failure must not stop polling.
        }
    };
}

function BuildDetailUrl(detail_base_url, shipment_id) {
    return `${String(detail_base_url).replace(/\/$/, '')}/${encodeURIComponent(String(shipment_id))}`;
}

function CreateJsonRequestHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

async function FetchCentreSummary(fetch_implementation, summary_url) {
    const response = await fetch_implementation(summary_url, {
        headers: CreateJsonRequestHeaders(),
    });

    if (!response.ok) {
        const error = new Error(`Centre summary request failed with status ${response.status}`);
        error.status = response.status;
        throw error;
    }

    return response.json();
}

function RenderCentreBadgeCount(document_object, pending_count) {
    const badge_text = FormatBadgeCount(pending_count);
    document_object.querySelectorAll('[data-book-shipment-badge="centre"]').forEach((badge_element) => {
        badge_element.textContent = badge_text;
        badge_element.hidden = badge_text === '';
        badge_element.setAttribute(
            'aria-label',
            badge_text === ''
                ? 'ไม่มีหนังสือที่รอรับ'
                : `มีรายการจัดส่งหนังสือรอตรวจรับ ${pending_count} รายการ`,
        );
    });
}

function RenderCentreToast(root_element, index_url, detail_base_url, toast_event) {
    const toast_element = root_element.querySelector('[data-book-shipment-alert-toast]');
    const title_element = root_element.querySelector('[data-book-shipment-alert-title]');
    const message_element = root_element.querySelector('[data-book-shipment-alert-message]');
    const action_element = root_element.querySelector('[data-book-shipment-alert-centre-action]');

    if (!toast_element || !title_element || !message_element || !action_element) {
        return;
    }

    const is_new_shipment = toast_event.event_type === 'new_shipment';
    const latest_shipment = toast_event.latest_shipment;
    title_element.textContent = is_new_shipment
        ? 'มีรายการจัดส่งหนังสือใหม่จากศูนย์จัดส่ง'
        : 'รายการหนังสือที่กำลังจัดส่งเข้า';
    message_element.textContent = is_new_shipment
        ? `${latest_shipment.shipment_number} มีหนังสือ ${latest_shipment.item_count} เล่มรอตรวจรับ`
        : `มีรายการจัดส่งหนังสือจากศูนย์จัดส่ง ${toast_event.pending_count} รายการรอตรวจรับ`;
    action_element.textContent = is_new_shipment ? 'ดูรายการจัดส่ง' : 'ดูรายการที่กำลังจัดส่งเข้า';
    action_element.href = is_new_shipment
        ? BuildDetailUrl(detail_base_url, latest_shipment.id)
        : index_url;
    action_element.hidden = false;
    toast_element.hidden = false;
};

export function CreateCentreIncomingShipmentAlert(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const summary_url = root_element?.getAttribute('data-summary-url');
    const index_url = root_element?.getAttribute('data-index-url');
    const detail_base_url = root_element?.getAttribute('data-detail-base-url');
    const storage_key = root_element?.getAttribute('data-storage-key');

    if (!root_element || !document_object || !summary_url || !index_url || !detail_base_url || !storage_key) {
        return null;
    }

    const storage = dependencies.Storage;
    const summary_handler = CreateCentreSummaryHandler({
        ReadCursor: () => storage.getItem(storage_key),
        WriteCursor: (next_cursor) => storage.setItem(storage_key, next_cursor),
        UpdateBadgeCount: (pending_count) => RenderCentreBadgeCount(document_object, pending_count),
        IndexUrl: index_url,
        ToastManager: dependencies.ToastManager,
        ShowToast: (toast_event) => RenderCentreToast(root_element, index_url, detail_base_url, toast_event),
    });

    const dismiss_element = root_element.querySelector('[data-book-shipment-alert-dismiss]');
    const toast_element = root_element.querySelector('[data-book-shipment-alert-toast]');
    dismiss_element?.addEventListener('click', () => {
        if (toast_element) {
            toast_element.hidden = true;
        }
    });

    const poller = CreateBookShipmentPoller({
        FetchSummary: () => FetchCentreSummary(dependencies.FetchImplementation, summary_url),
        HandleSummary: summary_handler,
        IsVisible: dependencies.IsVisible,
        ScheduleTimeout: dependencies.ScheduleTimeout,
        ClearScheduledTimeout: dependencies.ClearScheduledTimeout,
        AddVisibilityListener: dependencies.AddVisibilityListener,
        RemoveVisibilityListener: dependencies.RemoveVisibilityListener,
    });

    return { poller };
}

export function BootstrapCentreIncomingShipmentAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    FetchImplementation = globalThis.fetch?.bind(globalThis),
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !WindowObject || !FetchImplementation || !Storage) {
        return null;
    }

    const root_element = DocumentObject.querySelector('[data-book-shipment-alert-role="centre"]');
    if (!root_element) {
        return null;
    }

    const alert_client = CreateCentreIncomingShipmentAlert({
        RootElement: root_element,
        DocumentObject,
        Storage,
        FetchImplementation,
        ToastManager: GetPendingWorkAlertManager(),
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
    BootstrapCentreIncomingShipmentAlert();
}
