const REMINDER_INTERVAL_MS = 30 * 60 * 1000;
const WORK_SOURCE_IDS = ['affiliate', 'book'];
const SUMMARY_EVENT_TYPE = 'pending_summary';

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

function ReadLastShownAt(storage, storage_key) {
    try {
        const stored_value = storage.getItem(storage_key);
        if (stored_value === null) {
            return null;
        }

        const last_shown_at = Number(stored_value);
        return Number.isFinite(last_shown_at) && last_shown_at >= 0 ? last_shown_at : null;
    } catch (error) {
        return null;
    }
}

function WriteLastShownAt(storage, storage_key, last_shown_at) {
    try {
        storage.setItem(storage_key, String(last_shown_at));
    } catch (error) {
        // Storage is an enhancement; a transient storage failure must not stop alerts.
    }
}

export function CreatePendingWorkAlertManager(dependencies) {
    const root_element = dependencies.RootElement;
    const storage = dependencies.Storage;
    const storage_key = dependencies.StorageKey
        ?? root_element?.getAttribute('data-storage-key');
    const now = dependencies.Now ?? (() => Date.now());
    const reminder_interval_ms = dependencies.ReminderIntervalMs ?? REMINDER_INTERVAL_MS;
    const toast_element = root_element?.querySelector('[data-pending-work-alert-toast]');
    const dismiss_element = root_element?.querySelector('[data-pending-work-alert-dismiss]');

    if (!root_element || !storage || !IsNonEmptyString(storage_key) || !toast_element) {
        return null;
    }

    const work_items = new Map();
    let last_shown_at = ReadLastShownAt(storage, storage_key);
    let toast_visible = false;

    const HasPendingWork = () => Array.from(work_items.values())
        .some((work_item) => work_item.pending_count > 0);

    const IsReminderDue = () => last_shown_at === null
        || now() - last_shown_at >= reminder_interval_ms;

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

        toast_element.hidden = !toast_visible || !HasPendingWork();
    };

    const Dismiss = () => {
        toast_visible = false;
        last_shown_at = now();
        WriteLastShownAt(storage, storage_key, last_shown_at);
        Render();
    };

    dismiss_element?.addEventListener('click', Dismiss);

    const HandleWorkItem = (work_item) => {
        const normalized_work_item = NormalizeWorkItem(work_item);
        if (!normalized_work_item) {
            throw new Error('Invalid pending work alert item');
        }

        const is_immediate_event = normalized_work_item.event_type !== null;
        const display_message = normalized_work_item.event_type !== null
            && normalized_work_item.event_type !== SUMMARY_EVENT_TYPE
            ? normalized_work_item.new_message
            : normalized_work_item.reminder_message;
        work_items.set(normalized_work_item.source_id, {
            ...normalized_work_item,
            display_message,
        });

        if (!HasPendingWork()) {
            toast_visible = false;
            Render();
            return;
        }

        if (is_immediate_event || IsReminderDue()) {
            toast_visible = true;
            last_shown_at = now();
            WriteLastShownAt(storage, storage_key, last_shown_at);
        }

        Render();
    };

    Render();

    return {
        Dismiss,
        HandleWorkItem,
        IsToastVisible: () => toast_visible && HasPendingWork(),
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
