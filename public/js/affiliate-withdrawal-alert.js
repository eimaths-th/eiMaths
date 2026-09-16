import {
    CreateBookShipmentPoller,
    FormatBadgeCount,
} from './book-shipment-alert-poller.js';
import { GetPendingWorkAlertManager } from './pending-work-alert.js';

const REQUESTED_STATUS = 'REQUESTED';
const ACTIONABLE_STATUSES = ['REQUESTED', 'APPROVED'];
export const AFFILIATE_WITHDRAWALS_CHANGED_EVENT = 'affiliate-withdrawals:changed';

function IsPositiveInteger(value) {
    return Number.isSafeInteger(value) && value > 0;
}

function IsNonNegativeInteger(value) {
    return Number.isSafeInteger(value) && value >= 0;
}

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

function IsValidCursor(value) {
    return value === null || IsNonEmptyString(value);
}

function ValidateActionableWithdrawal(withdrawal) {
    if (
        !withdrawal
        || !IsPositiveInteger(withdrawal.id)
        || !IsNonEmptyString(withdrawal.affiliate_name)
        || !IsPositiveInteger(withdrawal.amount_minor)
        || !ACTIONABLE_STATUSES.includes(withdrawal.status)
        || !IsNonEmptyString(withdrawal.requested_at)
    ) {
        return null;
    }

    return {
        id: withdrawal.id,
        affiliate_name: withdrawal.affiliate_name,
        amount_minor: withdrawal.amount_minor,
        status: withdrawal.status,
        requested_at: withdrawal.requested_at,
    };
}

export function ValidateAffiliateWithdrawalSummary(summary) {
    const summary_data = summary?.data;
    if (
        !summary_data
        || !IsNonNegativeInteger(summary_data.actionable_count)
        || !IsNonNegativeInteger(summary_data.requested_count)
        || !IsNonNegativeInteger(summary_data.approved_count)
        || summary_data.actionable_count !== summary_data.requested_count + summary_data.approved_count
        || !IsValidCursor(summary_data.latest_event_cursor)
        || (summary_data.latest_event_status !== null && !IsNonEmptyString(summary_data.latest_event_status))
    ) {
        return null;
    }

    const latest_actionable_withdrawal = summary_data.latest_actionable_withdrawal === null
        ? null
        : ValidateActionableWithdrawal(summary_data.latest_actionable_withdrawal);
    if (
        (summary_data.latest_actionable_withdrawal !== null && latest_actionable_withdrawal === null)
        || (summary_data.actionable_count === 0 && latest_actionable_withdrawal !== null)
        || (summary_data.actionable_count > 0 && latest_actionable_withdrawal === null)
        || (summary_data.actionable_count > 0 && summary_data.latest_event_cursor === null)
    ) {
        return null;
    }

    return {
        actionable_count: summary_data.actionable_count,
        requested_count: summary_data.requested_count,
        approved_count: summary_data.approved_count,
        latest_event_cursor: summary_data.latest_event_cursor,
        latest_event_status: summary_data.latest_event_status,
        latest_actionable_withdrawal,
    };
}

function HasStoredCursor(cursor) {
    return IsNonEmptyString(cursor);
}

function FormatBaht(amount_minor) {
    return new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount_minor / 100);
}

function BuildReminderMessage(summary) {
    if (summary.actionable_count === 0) {
        return 'ไม่มีคำขอถอนเงินที่ต้องจัดการ';
    }

    const messages = [];
    if (summary.requested_count > 0) {
        messages.push(`รออนุมัติ ${summary.requested_count}`);
    }
    if (summary.approved_count > 0) {
        messages.push(`รอโอนเงิน ${summary.approved_count}`);
    }

    return `มีคำขอถอนเงินที่ต้องจัดการ ${summary.actionable_count} รายการ (${messages.join(', ')})`;
}

export function CreateAffiliateWithdrawalSummaryHandler(dependencies) {
    let in_memory_cursor = null;

    return async (summary) => {
        const validated_summary = ValidateAffiliateWithdrawalSummary(summary);
        if (!validated_summary) {
            throw new Error('Invalid Affiliate withdrawal summary');
        }

        dependencies.UpdateBadgeCount(validated_summary.actionable_count);

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
            && validated_summary.actionable_count > 0;
        const should_show_new_withdrawal = has_new_event
            && validated_summary.latest_event_status === REQUESTED_STATUS
            && validated_summary.latest_actionable_withdrawal?.status === REQUESTED_STATUS;
        const event_type = should_show_initial_summary
            ? 'pending_summary'
            : should_show_new_withdrawal
                ? 'new_withdrawal'
                : null;
        const latest_withdrawal = validated_summary.latest_actionable_withdrawal;
        const affiliate_name = latest_withdrawal?.affiliate_name ?? 'Affiliate';
        const amount = latest_withdrawal === null ? '0.00' : FormatBaht(latest_withdrawal.amount_minor);

        dependencies.ToastManager?.HandleWorkItem({
            source_id: 'withdrawal',
            pending_count: validated_summary.actionable_count,
            event_type,
            title: 'Affiliate withdrawal management',
            reminder_message: BuildReminderMessage(validated_summary),
            new_message: `${affiliate_name} ส่งคำขอถอนเงิน ${amount} บาท และกำลังรออนุมัติ (${validated_summary.actionable_count} รายการที่ต้องจัดการ)`,
            action_url: dependencies.IndexUrl,
            action_label: 'จัดการคำขอถอนเงิน',
        });

        if (latest_cursor !== null) {
            in_memory_cursor = latest_cursor;
            try {
                dependencies.WriteCursor(latest_cursor);
            } catch (error) {
                // A storage failure must not stop live summary updates.
            }
        }
    };
}

export function RenderAffiliateWithdrawalBadgeCount(document_object, actionable_count) {
    const badge_text = FormatBadgeCount(actionable_count);
    document_object.querySelectorAll('[data-affiliate-withdrawal-badge]').forEach((badge_element) => {
        badge_element.textContent = badge_text;
        badge_element.hidden = badge_text === '';
        badge_element.setAttribute(
            'aria-label',
            badge_text === ''
                ? 'ไม่มีคำขอถอนเงินที่ต้องจัดการ'
                : `มีคำขอถอนเงินที่ต้องจัดการ ${actionable_count} รายการ`,
        );
    });
}

function CreateJsonRequestHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

export function CreateAffiliateWithdrawalAlert(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const summary_url = root_element?.getAttribute('data-summary-url');
    const index_url = root_element?.getAttribute('data-index-url');
    const storage_key = root_element?.getAttribute('data-storage-key');

    if (!root_element || !document_object || !summary_url || !index_url || !storage_key) {
        return null;
    }

    const summary_handler = CreateAffiliateWithdrawalSummaryHandler({
        ReadCursor: () => dependencies.Storage.getItem(storage_key),
        WriteCursor: (next_cursor) => dependencies.Storage.setItem(storage_key, next_cursor),
        UpdateBadgeCount: (actionable_count) => RenderAffiliateWithdrawalBadgeCount(
            document_object,
            actionable_count,
        ),
        IndexUrl: index_url,
        ToastManager: dependencies.ToastManager,
    });

    const poller = CreateBookShipmentPoller({
        FetchSummary: async () => {
            const response = await dependencies.FetchImplementation(summary_url, {
                headers: CreateJsonRequestHeaders(),
            });
            if (!response.ok) {
                const error = new Error(`Affiliate withdrawal summary request failed with status ${response.status}`);
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

export function BootstrapAffiliateWithdrawalAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    FetchImplementation = globalThis.fetch?.bind(globalThis),
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !WindowObject || !FetchImplementation || !Storage) {
        return null;
    }

    const root_element = DocumentObject.querySelector('[data-affiliate-withdrawal-alert]');
    if (!root_element) {
        return null;
    }

    const alert_client = CreateAffiliateWithdrawalAlert({
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
    if (!alert_client) {
        return null;
    }

    WindowObject.addEventListener?.(AFFILIATE_WITHDRAWALS_CHANGED_EVENT, () => {
        if (typeof alert_client.poller.RequestRefresh === 'function') {
            alert_client.poller.RequestRefresh();
            return;
        }

        void alert_client.poller.Poll();
    });
    alert_client.poller.Start();

    return alert_client;
}

if (typeof globalThis.document !== 'undefined') {
    BootstrapAffiliateWithdrawalAlert();
}
