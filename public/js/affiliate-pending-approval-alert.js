import {
    CreateBookShipmentPoller,
    FormatBadgeCount,
} from './book-shipment-alert-poller.js';
import { GetPendingWorkAlertManager } from './pending-work-alert.js';

const PENDING_APPROVAL_STATUS = 'PENDING_APPROVAL';

function IsPositiveInteger(value) {
    return Number.isSafeInteger(value) && value > 0;
}

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

function IsValidCursor(value) {
    return value === null || IsNonEmptyString(value);
}

function ValidatePendingAffiliate(affiliate) {
    if (
        !affiliate
        || !IsPositiveInteger(affiliate.id)
        || !IsNonEmptyString(affiliate.name)
        || !IsNonEmptyString(affiliate.pending_at)
    ) {
        return null;
    }

    return {
        id: affiliate.id,
        name: affiliate.name,
        pending_at: affiliate.pending_at,
    };
}

export function ValidateAffiliatePendingApprovalSummary(summary) {
    const summary_data = summary?.data;
    if (
        !summary_data
        || !Number.isSafeInteger(summary_data.pending_count)
        || summary_data.pending_count < 0
        || !IsValidCursor(summary_data.latest_event_cursor)
        || (summary_data.latest_event_status !== null && !IsNonEmptyString(summary_data.latest_event_status))
    ) {
        return null;
    }

    const latest_pending_affiliate = summary_data.latest_pending_affiliate === null
        ? null
        : ValidatePendingAffiliate(summary_data.latest_pending_affiliate);
    if (
        (summary_data.latest_pending_affiliate !== null && latest_pending_affiliate === null)
        || (summary_data.pending_count === 0 && latest_pending_affiliate !== null)
        || (summary_data.pending_count > 0 && latest_pending_affiliate === null)
        || (summary_data.pending_count > 0 && summary_data.latest_event_cursor === null)
    ) {
        return null;
    }

    return {
        pending_count: summary_data.pending_count,
        latest_event_cursor: summary_data.latest_event_cursor,
        latest_event_status: summary_data.latest_event_status,
        latest_pending_affiliate,
    };
}

function HasStoredCursor(cursor) {
    return IsNonEmptyString(cursor);
}

export function CreateAffiliatePendingApprovalSummaryHandler(dependencies) {
    let in_memory_cursor = null;

    return async (summary) => {
        const validated_summary = ValidateAffiliatePendingApprovalSummary(summary);
        if (!validated_summary) {
            throw new Error('Invalid Affiliate pending approval summary');
        }

        dependencies.UpdateBadgeCount(validated_summary.pending_count);

        let stored_cursor = null;
        try {
            stored_cursor = dependencies.ReadCursor();
        } catch (error) {
            stored_cursor = in_memory_cursor;
        }
        if (!HasStoredCursor(stored_cursor) && HasStoredCursor(in_memory_cursor)) {
            stored_cursor = in_memory_cursor;
        }

        const latest_cursor = validated_summary.latest_event_cursor;
        const has_new_event = latest_cursor !== null && stored_cursor !== latest_cursor;
        const should_show_initial_summary = !HasStoredCursor(stored_cursor)
            && validated_summary.pending_count > 0;
        const should_show_new_application = has_new_event
            && validated_summary.latest_event_status === PENDING_APPROVAL_STATUS
            && validated_summary.latest_pending_affiliate !== null;

        const event_type = should_show_initial_summary
            ? 'pending_summary'
            : should_show_new_application
                ? 'new_application'
                : null;
        if (dependencies.ToastManager) {
            const latest_pending_affiliate = validated_summary.latest_pending_affiliate;
            const latest_name = latest_pending_affiliate?.name ?? 'Affiliate';
            dependencies.ToastManager.HandleWorkItem({
                source_id: 'affiliate',
                pending_count: validated_summary.pending_count,
                event_type,
                title: 'Affiliate account',
                reminder_message: `มี Affiliate รออนุมัติ ${validated_summary.pending_count} รายการ`,
                new_message: `${latest_name} สมัคร Affiliate และกำลังรออนุมัติ (${validated_summary.pending_count} รายการ)`,
                action_url: dependencies.IndexUrl,
                action_label: 'ดูรายการ Affiliate ที่รออนุมัติ',
            });
        } else if (event_type !== null) {
            dependencies.ShowToast({
                event_type,
                pending_count: validated_summary.pending_count,
                latest_pending_affiliate: validated_summary.latest_pending_affiliate,
            });
        }

        if (latest_cursor !== null) {
            in_memory_cursor = latest_cursor;
            try {
                dependencies.WriteCursor(latest_cursor);
            } catch (error) {
                // Storage is an enhancement; a transient storage failure must not stop polling.
            }
        }
    };
}

export function RenderAffiliatePendingApprovalBadgeCount(document_object, pending_count) {
    const badge_text = FormatBadgeCount(pending_count);
    document_object.querySelectorAll('[data-affiliate-pending-approval-badge]').forEach((badge_element) => {
        badge_element.textContent = badge_text;
        badge_element.hidden = badge_text === '';
        badge_element.setAttribute(
            'aria-label',
            badge_text === ''
                ? 'ไม่มี Affiliate รออนุมัติ'
                : `มี Affiliate รออนุมัติ ${pending_count} รายการ`,
        );
    });
}

export function RenderAffiliatePendingApprovalToast(root_element, index_url, toast_event) {
    const toast_element = root_element.querySelector('[data-affiliate-pending-approval-toast]');
    const title_element = root_element.querySelector('[data-affiliate-pending-approval-title]');
    const message_element = root_element.querySelector('[data-affiliate-pending-approval-message]');
    const action_element = root_element.querySelector('[data-affiliate-pending-approval-action]');
    if (!toast_element || !title_element || !message_element || !action_element) {
        return;
    }

    const latest_pending_affiliate = toast_event.latest_pending_affiliate;
    const is_new_application = toast_event.event_type === 'new_application';
    title_element.textContent = is_new_application
        ? 'มี Affiliate สมัครใหม่รออนุมัติ'
        : 'มี Affiliate รออนุมัติ';
    message_element.textContent = is_new_application && latest_pending_affiliate
        ? `${latest_pending_affiliate.name} สมัคร Affiliate และกำลังรออนุมัติ (${toast_event.pending_count} รายการ)`
        : `มี Affiliate รออนุมัติ ${toast_event.pending_count} รายการ`;
    action_element.href = index_url;
    action_element.hidden = false;
    toast_element.hidden = false;
}

function CreateJsonRequestHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

export function CreateAffiliatePendingApprovalAlert(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const summary_url = root_element?.getAttribute('data-summary-url');
    const index_url = root_element?.getAttribute('data-index-url');
    const storage_key = root_element?.getAttribute('data-storage-key');

    if (!root_element || !document_object || !summary_url || !index_url || !storage_key) {
        return null;
    }

    const summary_handler = CreateAffiliatePendingApprovalSummaryHandler({
        ReadCursor: () => dependencies.Storage.getItem(storage_key),
        WriteCursor: (next_cursor) => dependencies.Storage.setItem(storage_key, next_cursor),
        UpdateBadgeCount: (pending_count) => RenderAffiliatePendingApprovalBadgeCount(
            document_object,
            pending_count,
        ),
        IndexUrl: index_url,
        ToastManager: dependencies.ToastManager,
        ShowToast: (toast_event) => RenderAffiliatePendingApprovalToast(
            root_element,
            index_url,
            toast_event,
        ),
    });

    const dismiss_element = root_element.querySelector('[data-affiliate-pending-approval-dismiss]');
    const toast_element = root_element.querySelector('[data-affiliate-pending-approval-toast]');
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
                const error = new Error(`Affiliate pending approval summary request failed with status ${response.status}`);
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

export function BootstrapAffiliatePendingApprovalAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    FetchImplementation = globalThis.fetch?.bind(globalThis),
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !WindowObject || !FetchImplementation || !Storage) {
        return null;
    }

    const root_element = DocumentObject.querySelector('[data-affiliate-pending-approval-alert]');
    if (!root_element) {
        return null;
    }

    const alert_client = CreateAffiliatePendingApprovalAlert({
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
    BootstrapAffiliatePendingApprovalAlert();
}
