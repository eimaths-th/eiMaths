export const POLL_INTERVAL_MS = 60000;
export const RETRY_INTERVALS_MS = Object.freeze([120000, 300000]);

export function CreateAffiliateAnalyticsPoller(dependencies = {}) {
    const Fetcher = dependencies.Fetch ?? dependencies.fetch ?? globalThis.fetch;
    const SetTimeout = dependencies.SetTimeout ?? dependencies.setTimeout ?? globalThis.setTimeout;
    const ClearTimeout = dependencies.ClearTimeout ?? dependencies.clearTimeout ?? globalThis.clearTimeout;
    const HandleData = dependencies.HandleData ?? dependencies.handleData ?? (() => {});
    const HandleError = dependencies.HandleError ?? dependencies.handleError ?? (() => {});
    const ClearWarning = dependencies.ClearWarning ?? dependencies.clearWarning ?? (() => {});
    const IsHidden = dependencies.IsHidden ?? dependencies.isHidden ?? (() => globalThis.document?.visibilityState === 'hidden');
    const AddVisibilityListener = dependencies.AddVisibilityListener ?? dependencies.addVisibilityListener;
    const RemoveVisibilityListener = dependencies.RemoveVisibilityListener ?? dependencies.removeVisibilityListener;

    let stopped = true;
    let timer_id = null;
    let active_request = null;
    let request_sequence = 0;
    let failure_count = 0;
    let visibility_listener = null;

    function Start() {
        if (!stopped) return;
        stopped = false;
        AttachVisibilityListener();
        if (!IsHidden()) Poll();
    }

    function Stop() {
        stopped = true;
        request_sequence += 1;
        ClearScheduledPoll();
        AbortActiveRequest();
        DetachVisibilityListener();
    }

    function Poll() {
        if (stopped || IsHidden()) return Promise.resolve(null);
        if (active_request !== null) return active_request.promise;
        ClearScheduledPoll();
        const request_id = ++request_sequence;
        const controller = typeof AbortController === 'function' ? new AbortController() : null;
        active_request = { request_id, controller, promise: null };

        const operation = Promise.resolve()
            .then(() => Fetcher({ signal: controller?.signal, request_id }))
            .then(async (response) => {
                if (response?.status === 401 || response?.status === 403) {
                    Stop();
                    return null;
                }
                if (response?.ok === false) {
                    const error = new Error(`Affiliate analytics request failed with status ${response.status ?? 'unknown'}`);
                    error.status = response.status;
                    throw error;
                }
                return response && typeof response.json === 'function' ? response.json() : response;
            })
            .then((payload) => {
                if (stopped || request_id !== request_sequence) return payload;
                failure_count = 0;
                ClearWarning();
                HandleData(payload);
                Schedule(POLL_INTERVAL_MS);
                return payload;
            })
            .catch((error) => {
                if (stopped || request_id !== request_sequence) return null;
                if (error?.status === 401 || error?.status === 403) {
                    Stop();
                    return null;
                }
                failure_count += 1;
                HandleError(error);
                const retry_index = Math.min(failure_count - 1, RETRY_INTERVALS_MS.length - 1);
                Schedule(RETRY_INTERVALS_MS[retry_index]);
                return null;
            })
            .finally(() => {
                if (active_request?.request_id === request_id) active_request = null;
            });
        active_request.promise = operation;
        return operation;
    }

    function RetryNow() {
        ClearScheduledPoll();
        AbortActiveRequest();
        request_sequence += 1;
        failure_count = 0;
        if (!stopped && !IsHidden()) return Poll();
        return Promise.resolve(null);
    }

    function HandleVisibilityChange() {
        if (stopped) return;
        if (IsHidden()) {
            ClearScheduledPoll();
            AbortActiveRequest();
            request_sequence += 1;
            return;
        }
        Poll();
    }

    function IsStopped() {
        return stopped;
    }

    function Schedule(delay) {
        if (stopped || IsHidden()) return;
        ClearScheduledPoll();
        timer_id = SetTimeout(() => {
            timer_id = null;
            return Poll();
        }, delay);
    }

    function ClearScheduledPoll() {
        if (timer_id !== null) {
            ClearTimeout(timer_id);
            timer_id = null;
        }
    }

    function AbortActiveRequest() {
        active_request?.controller?.abort();
        active_request = null;
    }

    function AttachVisibilityListener() {
        visibility_listener = HandleVisibilityChange;
        if (AddVisibilityListener) {
            AddVisibilityListener(visibility_listener);
        } else if (globalThis.document?.addEventListener) {
            globalThis.document.addEventListener('visibilitychange', visibility_listener);
        }
    }

    function DetachVisibilityListener() {
        if (visibility_listener === null) return;
        if (RemoveVisibilityListener) {
            RemoveVisibilityListener(visibility_listener);
        } else if (globalThis.document?.removeEventListener) {
            globalThis.document.removeEventListener('visibilitychange', visibility_listener);
        }
        visibility_listener = null;
    }

    return { Start, Stop, Poll, RetryNow, HandleVisibilityChange, IsStopped };
}
