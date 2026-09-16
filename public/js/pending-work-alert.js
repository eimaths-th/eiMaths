const WORK_SOURCE_IDS = ['affiliate', 'course_application', 'withdrawal', 'book'];
const AFFILIATE_MANAGEMENT_SOURCE_IDS = ['affiliate', 'course_application', 'withdrawal'];
const SUMMARY_EVENT_TYPE = 'pending_summary';
const MAX_BADGE_COUNT = 99;

function IsNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== '';
}

function IsValidEventType(value) {
    return value === null || IsNonEmptyString(value);
}

function NormalizeWorkItem(work_item) {
    if (
        !work_item
        || !IsNonEmptyString(work_item.source_id)
        || !WORK_SOURCE_IDS.includes(work_item.source_id)
        || !Number.isSafeInteger(work_item.pending_count)
        || work_item.pending_count < 0
        || !IsValidEventType(work_item.event_type)
        || !IsNonEmptyString(work_item.title)
        || !IsNonEmptyString(work_item.reminder_message)
        || !IsNonEmptyString(work_item.new_message)
        || !IsNonEmptyString(work_item.action_url)
        || !IsNonEmptyString(work_item.action_label)
    ) {
        return null;
    }

    return {
        source_id: work_item.source_id,
        pending_count: work_item.pending_count,
        event_type: work_item.event_type,
        title: work_item.title,
        reminder_message: work_item.reminder_message,
        new_message: work_item.new_message,
        action_url: work_item.action_url,
        action_label: work_item.action_label,
    };
}

export function CreatePendingWorkAlertManager(dependencies) {
    const root_element = dependencies.RootElement;
    const document_object = dependencies.DocumentObject;
    const toggle_element = root_element?.querySelector('[data-pending-work-alert-toggle]');
    const badge_element = root_element?.querySelector('[data-pending-work-alert-badge]');
    const total_element = root_element?.querySelector('[data-pending-work-alert-total]');
    const panel_element = root_element?.querySelector('[data-pending-work-alert-panel]');
    const empty_element = root_element?.querySelector('[data-pending-work-alert-empty]');
    const dismiss_element = root_element?.querySelector('[data-pending-work-alert-dismiss]');

    if (!root_element || !toggle_element || !badge_element || !panel_element) {
        return null;
    }

    const work_items = new Map();
    let panel_open = false;

    const GetPendingWorkCount = () => WORK_SOURCE_IDS.reduce((total_count, source_id) => {
        const work_item = work_items.get(source_id);
        if (!work_item || work_item.pending_count <= 0) {
            return total_count;
        }

        return Math.min(Number.MAX_SAFE_INTEGER, total_count + work_item.pending_count);
    }, 0);

    const GetAffiliateManagementPendingCount = () => AFFILIATE_MANAGEMENT_SOURCE_IDS.reduce(
        (total_count, source_id) => {
            const work_item = work_items.get(source_id);
            if (!work_item || work_item.pending_count <= 0) {
                return total_count;
            }

            return Math.min(Number.MAX_SAFE_INTEGER, total_count + work_item.pending_count);
        },
        0,
    );

    const HasPendingWork = () => GetPendingWorkCount() > 0;

    const RenderWorkItem = (work_item) => {
        const item_element = root_element.querySelector(
            `[data-pending-work-item="${work_item.source_id}"]`,
        );
        if (!item_element) {
            return;
        }

        const title_element = item_element.querySelector('[data-pending-work-item-title]');
        const count_element = item_element.querySelector('[data-pending-work-item-count]');
        const message_element = item_element.querySelector('[data-pending-work-item-message]');
        const action_element = item_element.querySelector('[data-pending-work-item-action]');
        if (!title_element || !count_element || !message_element || !action_element) {
            return;
        }

        const has_pending_work = work_item.pending_count > 0;
        item_element.hidden = !has_pending_work;
        if (!has_pending_work) {
            return;
        }

        title_element.textContent = work_item.title;
        count_element.textContent = String(work_item.pending_count);
        message_element.textContent = work_item.display_message;
        action_element.textContent = work_item.action_label;
        action_element.href = work_item.action_url;
        action_element.hidden = false;
    };

    const Render = () => {
        const pending_work_count = GetPendingWorkCount();
        const has_pending_work = pending_work_count > 0;

        WORK_SOURCE_IDS.forEach((source_id) => {
            const work_item = work_items.get(source_id);
            if (work_item) {
                RenderWorkItem(work_item);
                return;
            }

            const item_element = root_element.querySelector(`[data-pending-work-item="${source_id}"]`);
            if (item_element) {
                item_element.hidden = true;
            }
        });

        badge_element.textContent = pending_work_count > MAX_BADGE_COUNT
            ? `${MAX_BADGE_COUNT}+`
            : String(pending_work_count);
        badge_element.hidden = !has_pending_work;
        badge_element.setAttribute(
            'aria-label',
            has_pending_work
                ? `มีงานที่ต้องจัดการ ${pending_work_count} รายการ`
                : 'ไม่มีงานที่ต้องจัดการ',
        );

        const affiliate_management_count = GetAffiliateManagementPendingCount();
        const affiliate_management_badge_text = affiliate_management_count > MAX_BADGE_COUNT
            ? `${MAX_BADGE_COUNT}+`
            : String(affiliate_management_count);
        document_object?.querySelectorAll?.('[data-affiliate-management-pending-badge]')
            .forEach((affiliate_badge_element) => {
                affiliate_badge_element.textContent = affiliate_management_badge_text;
                affiliate_badge_element.hidden = affiliate_management_count === 0;
                affiliate_badge_element.setAttribute(
                    'aria-label',
                    affiliate_management_count > 0
                        ? `มีงาน Affiliate ค้าง ${affiliate_management_count} รายการ`
                        : 'ไม่มีงาน Affiliate ค้าง',
                );
            });

        if (total_element) {
            total_element.textContent = has_pending_work
                ? `${pending_work_count} รายการ`
                : 'ไม่มีงานค้าง';
        }

        if (empty_element) {
            empty_element.hidden = has_pending_work;
        }

        panel_element.hidden = !panel_open;
        toggle_element.setAttribute('aria-expanded', panel_open ? 'true' : 'false');
        toggle_element.setAttribute(
            'aria-label',
            has_pending_work
                ? `ดูงานที่ต้องจัดการ มี ${pending_work_count} รายการ`
                : 'ดูงานที่ต้องจัดการ',
        );
    };

    const Open = () => {
        panel_open = true;
        Render();
    };

    const Close = () => {
        panel_open = false;
        Render();
    };

    const Toggle = (event) => {
        event?.preventDefault?.();
        if (panel_open) {
            Close();
            return;
        }

        Open();
    };

    const HandleOutsideClick = (event) => {
        if (!panel_open || typeof root_element.contains !== 'function') {
            return;
        }

        if (!root_element.contains(event.target)) {
            Close();
        }
    };

    const HandleKeyDown = (event) => {
        if (event.key !== 'Escape' || !panel_open) {
            return;
        }

        Close();
        toggle_element.focus?.();
    };

    toggle_element.addEventListener('click', Toggle);
    dismiss_element?.addEventListener('click', (event) => {
        event?.preventDefault?.();
        Close();
        toggle_element.focus?.();
    });
    document_object?.addEventListener?.('click', HandleOutsideClick);
    document_object?.addEventListener?.('keydown', HandleKeyDown);

    const HandleWorkItem = (work_item) => {
        const normalized_work_item = NormalizeWorkItem(work_item);
        if (!normalized_work_item) {
            throw new Error('Invalid pending work alert item');
        }

        const display_message = normalized_work_item.event_type !== null
            && normalized_work_item.event_type !== SUMMARY_EVENT_TYPE
            ? normalized_work_item.new_message
            : normalized_work_item.reminder_message;
        work_items.set(normalized_work_item.source_id, {
            ...normalized_work_item,
            display_message,
        });

        Render();
    };

    Render();

    return {
        Open,
        Close,
        Dismiss: Close,
        Toggle,
        HandleWorkItem,
        HasPendingWork,
        IsPanelOpen: () => panel_open,
        IsToastVisible: () => panel_open,
        Render,
    };
}

export function GetPendingWorkAlertManager() {
    return globalThis.PendingWorkAlertManager ?? null;
}

export function BootstrapPendingWorkAlert({
    DocumentObject = globalThis.document,
    WindowObject = globalThis.window,
    Storage = WindowObject?.localStorage,
} = {}) {
    if (!DocumentObject || !Storage) {
        return null;
    }

    const existing_manager = GetPendingWorkAlertManager();
    if (existing_manager) {
        return existing_manager;
    }

    const root_element = DocumentObject.querySelector('[data-pending-work-alert]');
    if (!root_element) {
        return null;
    }

    const alert_manager = CreatePendingWorkAlertManager({
        RootElement: root_element,
        DocumentObject,
        Storage,
    });
    if (alert_manager) {
        globalThis.PendingWorkAlertManager = alert_manager;
    }

    return alert_manager;
}

if (typeof globalThis.document !== 'undefined') {
    BootstrapPendingWorkAlert();
}
