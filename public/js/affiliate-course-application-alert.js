import {
    CreateBookShipmentPoller,
    FormatBadgeCount,
} from './book-shipment-alert-poller.js';
import { GetPendingWorkAlertManager } from './pending-work-alert.js';

const PENDING_STATUS = '1';
export const COURSE_APPLICATION_CHANGED_EVENT = 'affiliate-course-applications:changed';

function IsPositiveInteger(value) {
    return Number.isSafeInteger(value) && value > 0;
}

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

function IsValidCursor(value) {
    return value === null || IsNonEmptyString(value);
}

function ValidatePendingApplication(application) {
    if (
        !application
        || !IsPositiveInteger(application.id)
        || !IsNonEmptyString(application.applicant_name)
        || !IsNonEmptyString(application.course_name)
        || !IsNonEmptyString(application.branch_name)
        || (application.submitted_at !== null && !IsNonEmptyString(application.submitted_at))
    ) {
        return null;
    }

    return {
        id: application.id,
        applicant_name: application.applicant_name,
        course_name: application.course_name,
        branch_name: application.branch_name,
        submitted_at: application.submitted_at,
    };
}

export function ValidateAffiliateCourseApplicationSummary(summary) {
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

    const latest_pending_application = summary_data.latest_pending_application === null
        ? null
        : ValidatePendingApplication(summary_data.latest_pending_application);
    if (
        (summary_data.latest_pending_application !== null && latest_pending_application === null)
        || (summary_data.pending_count === 0 && latest_pending_application !== null)
        || (summary_data.pending_count > 0 && latest_pending_application === null)
        || (summary_data.pending_count > 0 && summary_data.latest_event_cursor === null)
    ) {
        return null;
    }

    return {
        pending_count: summary_data.pending_count,
        latest_event_cursor: summary_data.latest_event_cursor,
        latest_event_status: summary_data.latest_event_status,
        latest_pending_application,
    };
}

function HasStoredCursor(cursor) {
    return IsNonEmptyString(cursor);
}

export function CreateAffiliateCourseApplicationSummaryHandler(dependencies) {
    let in_memory_cursor = null;

    return async (summary) => {
        const validated_summary = ValidateAffiliateCourseApplicationSummary(summary);
        if (!validated_summary) {
            throw new Error('Invalid Affiliate course application summary');
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
            && validated_summary.latest_event_status === PENDING_STATUS
            && validated_summary.latest_pending_application !== null;
        const event_type = should_show_initial_summary
            ? 'pending_summary'
            : should_show_new_application
                ? 'new_application'
                : null;
        const latest_application = validated_summary.latest_pending_application;
        const applicant_name = latest_application?.applicant_name ?? 'ผู้สมัครคอร์ส';
        const course_name = latest_application?.course_name ?? 'ไม่ระบุคอร์ส';
        const branch_name = latest_application?.branch_name ?? 'ไม่ระบุสาขา';

        if (dependencies.ToastManager) {
            dependencies.ToastManager.HandleWorkItem({
                source_id: 'course_application',
                pending_count: validated_summary.pending_count,
                event_type,
                title: 'Affiliate Course Applications',
                reminder_message: `มีใบสมัครคอร์สรอดำเนินการ ${validated_summary.pending_count} รายการ`,
                new_message: `${applicant_name} สมัครคอร์ส ${course_name} ที่สาขา ${branch_name} และกำลังรอดำเนินการ (${validated_summary.pending_count} รายการ)`,
                action_url: dependencies.IndexUrl,
                action_label: 'ดูใบสมัครคอร์ส',
            });
        } else if (event_type !== null) {
            dependencies.ShowAlert?.({
                event_type,
                pending_count: validated_summary.pending_count,
                latest_pending_application: latest_application,
            });
        }

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

export function RenderAffiliateCourseApplicationBadgeCount(document_object, pending_count) {
    const badge_text = FormatBadgeCount(pending_count);
    document_object.querySelectorAll('[data-affiliate-course-application-badge]').forEach((badge_element) => {
        badge_element.textContent = badge_text;
        badge_element.hidden = badge_text === '';
        badge_element.setAttribute(
            'aria-label',
            badge_text === ''
                ? 'ไม่มีใบสมัครคอร์สรอดำเนินการ'
                : `มีใบสมัครคอร์สรอดำเนินการ ${pending_count} รายการ`,
        );
    });
}

function CreateJsonRequestHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

export function CreateAffiliateCourseApplicationAlert(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const summary_url = root_element?.getAttribute('data-summary-url');
    const index_url = root_element?.getAttribute('data-index-url');
    const storage_key = root_element?.getAttribute('data-storage-key');

    if (!root_element || !document_object || !summary_url || !index_url || !storage_key) {
        return null;
    }

    const summary_handler = CreateAffiliateCourseApplicationSummaryHandler({
        ReadCursor: () => dependencies.Storage.getItem(storage_key),
        WriteCursor: (next_cursor) => dependencies.Storage.setItem(storage_key, next_cursor),
        UpdateBadgeCount: (pending_count) => RenderAffiliateCourseApplicationBadgeCount(
            document_object,
            pending_count,
        ),
        IndexUrl: index_url,
        ToastManager: dependencies.ToastManager,
        ShowAlert: dependencies.ShowAlert,
    });

    const poller = CreateBookShipmentPoller({
        FetchSummary: async () => {
            const response = await dependencies.FetchImplementation(summary_url, {
                headers: CreateJsonRequestHeaders(),
            });
            if (!response.ok) {
                const error = new Error(`Affiliate course application summary request failed with status ${response.status}`);
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

export function BootstrapAffiliateCourseApplicationAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    FetchImplementation = globalThis.fetch?.bind(globalThis),
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !WindowObject || !FetchImplementation || !Storage) {
        return null;
    }

    const root_element = DocumentObject.querySelector('[data-affiliate-course-application-alert]');
    if (!root_element) {
        return null;
    }

    const alert_client = CreateAffiliateCourseApplicationAlert({
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

    WindowObject.addEventListener?.(COURSE_APPLICATION_CHANGED_EVENT, () => {
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
    BootstrapAffiliateCourseApplicationAlert();
}
