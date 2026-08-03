export const POLL_INTERVAL_MS = 30000;
export const MAX_RETRY_INTERVAL_MS = 120000;

export function FormatBadgeCount(alert_count) {
    if (!Number.isInteger(alert_count) || alert_count <= 0) {
        return '';
    }

    return alert_count > 99 ? '99+' : String(alert_count);
}

export function BuildEventCursor(event_time, event_id) {
    if (
        typeof event_time !== 'string'
        || event_time.trim() === ''
        || (typeof event_id !== 'string' && typeof event_id !== 'number')
        || String(event_id).trim() === ''
    ) {
        return null;
    }

    return `${event_time}|${event_id}`;
}

export function CreateBookShipmentPoller(dependencies) {
    let timer_id = null;
    let is_request_in_flight = false;
    let is_stopped = false;
    let is_started = false;
    let retry_interval_ms = POLL_INTERVAL_MS;

    const ClearTimer = () => {
        if (timer_id !== null) {
            dependencies.ClearScheduledTimeout(timer_id);
            timer_id = null;
        }
    };

    const ScheduleNext = (delay_ms) => {
        ClearTimer();
        if (is_stopped || !dependencies.IsVisible()) {
            return;
        }

        timer_id = dependencies.ScheduleTimeout(() => {
            timer_id = null;
            void Poll();
        }, delay_ms);
    };

    const Stop = () => {
        if (is_stopped && !is_started) {
            return;
        }

        is_stopped = true;
        ClearTimer();
        if (is_started) {
            dependencies.RemoveVisibilityListener(HandleVisibilityChange);
        }
        is_started = false;
    };

    const Poll = async () => {
        if (is_stopped || is_request_in_flight || !dependencies.IsVisible()) {
            return;
        }

        is_request_in_flight = true;
        try {
            const summary = await dependencies.FetchSummary();
            await dependencies.HandleSummary(summary);
            retry_interval_ms = POLL_INTERVAL_MS;
            ScheduleNext(POLL_INTERVAL_MS);
        } catch (error) {
            if (error?.status === 401 || error?.status === 403) {
                Stop();
            } else {
                ScheduleNext(retry_interval_ms);
                retry_interval_ms = Math.min(retry_interval_ms * 2, MAX_RETRY_INTERVAL_MS);
            }
        } finally {
            is_request_in_flight = false;
        }
    };

    const HandleVisibilityChange = async () => {
        ClearTimer();
        if (!is_stopped && dependencies.IsVisible()) {
            await Poll();
        }
    };

    const Start = () => {
        if (is_started) {
            return;
        }

        is_started = true;
        is_stopped = false;
        dependencies.AddVisibilityListener(HandleVisibilityChange);
        void Poll();
    };

    return {
        Start,
        Stop,
        Poll,
        HandleVisibilityChange,
        IsStopped: () => is_stopped,
    };
}
