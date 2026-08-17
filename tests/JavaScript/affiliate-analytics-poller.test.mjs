import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CreateAffiliateAnalyticsPoller,
    POLL_INTERVAL_MS,
    RETRY_INTERVALS_MS,
} from '../../public/js/affiliate-analytics-poller.js';

function CreatePollerHarness() {
    const timers = new Map();
    const pending_requests = [];
    const visibility_listeners = new Set();
    let next_timer_id = 1;
    let hidden = false;

    const set_timeout = (callback, delay) => {
        const timer_id = next_timer_id++;
        timers.set(timer_id, { callback, delay });
        return timer_id;
    };

    const clear_timeout = (timer_id) => timers.delete(timer_id);

    const fetcher = ({ signal, request_id }) => new Promise((resolve, reject) => {
        pending_requests.push({ resolve, reject, signal, request_id });
    });

    const run_timer = (delay) => {
        const entry = [...timers.entries()].find(([, timer]) => timer.delay === delay);
        assert.ok(entry, `Expected a timer with delay ${delay}.`);
        timers.delete(entry[0]);
        return entry[1].callback();
    };

    return {
        timers,
        pending_requests,
        visibility_listeners,
        set_timeout,
        clear_timeout,
        fetcher,
        run_timer,
        set_hidden: (value) => { hidden = value; },
        is_hidden: () => hidden,
        add_visibility_listener: (listener) => visibility_listeners.add(listener),
        remove_visibility_listener: (listener) => visibility_listeners.delete(listener),
    };
}

function CreateResponse(payload, status = 200) {
    return { ok: status >= 200 && status < 300, status, json: async () => payload };
}

async function FlushMicrotasks() {
    for (let iteration = 0; iteration < 8; iteration += 1) await Promise.resolve();
}

function CreatePoller(harness, overrides = {}) {
    const handled_data = [];
    const handled_errors = [];
    const cleared_warnings = [];
    const poller = CreateAffiliateAnalyticsPoller({
        Fetch: harness.fetcher,
        SetTimeout: harness.set_timeout,
        ClearTimeout: harness.clear_timeout,
        IsHidden: harness.is_hidden,
        AddVisibilityListener: harness.add_visibility_listener,
        RemoveVisibilityListener: harness.remove_visibility_listener,
        HandleData: (payload) => handled_data.push(payload),
        HandleError: (error) => handled_errors.push(error),
        ClearWarning: () => cleared_warnings.push(true),
        ...overrides,
    });
    return { poller, handled_data, handled_errors, cleared_warnings };
}

test('StartsImmediatelyAndSchedulesTheNextSuccessfulPollAtSixtySeconds', async () => {
    const harness = CreatePollerHarness();
    const { poller, handled_data, cleared_warnings } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    assert.equal(harness.pending_requests.length, 1);

    harness.pending_requests[0].resolve(CreateResponse({ version: 1 }));
    await FlushMicrotasks();

    assert.deepEqual(handled_data, [{ version: 1 }]);
    assert.equal(cleared_warnings.length, 1);
    assert.equal([...harness.timers.values()][0].delay, POLL_INTERVAL_MS);
});

test('DoesNotOverlapRequestsWhileARequestIsPending', async () => {
    const harness = CreatePollerHarness();
    const { poller } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    poller.Poll();
    await FlushMicrotasks();

    assert.equal(harness.pending_requests.length, 1);
});

test('PausesHiddenPollingAndResumesAfterVisibilityReturns', async () => {
    const harness = CreatePollerHarness();
    const { poller } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    const first_request = harness.pending_requests[0];

    harness.set_hidden(true);
    poller.HandleVisibilityChange();
    assert.equal(first_request.signal.aborted, true);
    assert.equal(harness.timers.size, 0);

    harness.set_hidden(false);
    poller.HandleVisibilityChange();
    await FlushMicrotasks();
    assert.equal(harness.pending_requests.length, 2);
    assert.equal(harness.pending_requests[1].signal.aborted, false);
});

test('IgnoresAStaleResponseAfterManualRetry', async () => {
    const harness = CreatePollerHarness();
    const { poller, handled_data } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    const stale_request = harness.pending_requests[0];
    const retry_promise = poller.RetryNow();
    await FlushMicrotasks();
    const current_request = harness.pending_requests[1];

    stale_request.resolve(CreateResponse({ version: 'stale' }));
    await FlushMicrotasks();
    current_request.resolve(CreateResponse({ version: 'current' }));
    await retry_promise;
    await FlushMicrotasks();

    assert.deepEqual(handled_data, [{ version: 'current' }]);
});

test('UsesBoundedRetryIntervalsAndResetsAfterARecovery', async () => {
    const harness = CreatePollerHarness();
    const { poller, handled_errors, cleared_warnings } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    harness.pending_requests[0].resolve(CreateResponse({}, 500));
    await FlushMicrotasks();
    assert.equal(handled_errors.length, 1);
    assert.equal([...harness.timers.values()][0].delay, RETRY_INTERVALS_MS[0]);

    const second_poll = harness.run_timer(RETRY_INTERVALS_MS[0]);
    await FlushMicrotasks();
    harness.pending_requests[1].resolve(CreateResponse({}, 503));
    await second_poll;
    await FlushMicrotasks();
    assert.equal(handled_errors.length, 2);
    assert.equal([...harness.timers.values()][0].delay, RETRY_INTERVALS_MS[1]);

    const third_poll = harness.run_timer(RETRY_INTERVALS_MS[1]);
    await FlushMicrotasks();
    harness.pending_requests[2].resolve(CreateResponse({ recovered: true }));
    await third_poll;
    await FlushMicrotasks();
    assert.equal(cleared_warnings.length, 1);
    assert.equal([...harness.timers.values()][0].delay, POLL_INTERVAL_MS);
});

test('StopsAndDetachesAfterAnAuthenticationResponse', async () => {
    const harness = CreatePollerHarness();
    const { poller } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    harness.pending_requests[0].resolve(CreateResponse({}, 401));
    await FlushMicrotasks();

    assert.equal(poller.IsStopped(), true);
    assert.equal(harness.visibility_listeners.size, 0);
    assert.equal(harness.timers.size, 0);
});

test('ManualRetryCancelsTheScheduledTimerAndStartsImmediately', async () => {
    const harness = CreatePollerHarness();
    const { poller } = CreatePoller(harness);

    poller.Start();
    await FlushMicrotasks();
    harness.pending_requests[0].resolve(CreateResponse({ ready: true }));
    await FlushMicrotasks();
    assert.equal(harness.timers.size, 1);

    poller.RetryNow();
    await FlushMicrotasks();
    assert.equal(harness.timers.size, 0);
    assert.equal(harness.pending_requests.length, 2);
});
