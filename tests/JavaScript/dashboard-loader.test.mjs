import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { CreateDashboardLoader } from '../../public/js/dashboard-loader.js';

function deferred() {
    let resolve, reject;
    const promise = new Promise((yes, no) => { resolve = yes; reject = no; });
    return { promise, resolve, reject };
}

const tick = () => new Promise(resolve => setImmediate(resolve));

test('all nine data requests and the last chart render finish before revealing data', async () => {
    const requests = Array.from({ length: 9 }, deferred);
    const chart = deferred();
    const states = [];
    const loader = CreateDashboardLoader({
        getTasks: () => requests.map((request, index) => async () => {
            await request.promise;
            if (index === 8) await chart.promise;
        }),
        onStateChange: state => states.push(state),
    });

    const load = loader.load();
    assert.deepEqual(states, ['loading']);
    requests.slice(0, 8).forEach(request => request.resolve());
    await tick();
    assert.deepEqual(states, ['loading']);
    requests[8].resolve();
    await tick();
    assert.deepEqual(states, ['loading'], 'receiving the data is not enough if chart rendering is pending');
    chart.resolve();
    assert.equal(await load, true);
    assert.deepEqual(states, ['loading', 'ready']);
    assert.equal(loader.isLoading(), false);
});

test('repeated search clicks share the active load instead of starting conflicting requests', async () => {
    const request = deferred();
    let calls = 0;
    const loader = CreateDashboardLoader({
        getTasks: () => [() => { calls++; return request.promise; }],
        onStateChange: () => {},
    });
    const first = loader.load();
    const second = loader.load();
    assert.equal(first, second);
    assert.equal(loader.isLoading(), true);
    await tick();
    assert.equal(calls, 1);
    request.resolve();
    await first;
});

test('failure stays hidden, drains late responses, and allows a clean full retry', async () => {
    const lateResponse = deferred();
    const failure = new Error('request timed out');
    const states = [], errors = [];
    let attempts = 0;
    const loader = CreateDashboardLoader({
        getTasks: () => ++attempts === 1
            ? [() => Promise.reject(failure), () => lateResponse.promise]
            : [() => Promise.resolve('new data'), () => Promise.resolve('new chart')],
        onStateChange: state => states.push(state),
        onError: error => errors.push(error),
    });
    const load = loader.load();
    await tick();
    assert.deepEqual(states, ['loading']);
    assert.equal(loader.load(), load);
    lateResponse.resolve('old data');
    assert.equal(await load, false);
    assert.deepEqual(states, ['loading', 'error']);
    assert.deepEqual(errors, [failure]);
    assert.equal(await loader.load(), true);
    assert.deepEqual(states, ['loading', 'error', 'loading', 'ready']);
    assert.equal(attempts, 2);
});

test('chart rejection prevents partially rendered dashboard from being revealed', async () => {
    const states = [];
    const loader = CreateDashboardLoader({
        getTasks: () => [async () => {
            await Promise.resolve('valid response');
            throw new Error('chart failed to render');
        }],
        onStateChange: state => states.push(state),
    });
    assert.equal(await loader.load(), false);
    assert.deepEqual(states, ['loading', 'error']);
});

test('a synchronous task/setup failure releases the loader for retry', async () => {
    let fail = true;
    const states = [];
    const loader = CreateDashboardLoader({
        getTasks: () => {
            if (fail) throw new Error('invalid filter');
            return [() => 0];
        },
        onStateChange: state => states.push(state),
    });
    assert.equal(await loader.load(), false);
    assert.equal(loader.isLoading(), false);
    fail = false;
    assert.equal(await loader.load(), true);
    assert.deepEqual(states, ['loading', 'error', 'loading', 'ready']);
});

test('an empty branch with zero metrics and empty chart data is still a completed load', async () => {
    const states = [];
    const loader = CreateDashboardLoader({
        getTasks: () => [() => 0, () => [], () => ({}), () => undefined],
        onStateChange: state => states.push(state),
    });
    assert.equal(await loader.load(), true);
    assert.deepEqual(states, ['loading', 'ready']);
});

test('changing a filter starts a fresh hidden generation after the previous one completes', async () => {
    let selectedCentre = 'HQ';
    const received = [], states = [];
    const loader = CreateDashboardLoader({
        getTasks: () => {
            const centre = selectedCentre;
            return [() => received.push(centre)];
        },
        onStateChange: state => states.push(state),
    });
    await loader.load();
    selectedCentre = 'new-branch';
    await loader.load();
    assert.deepEqual(received, ['HQ', 'new-branch']);
    assert.deepEqual(states, ['loading', 'ready', 'loading', 'ready']);
});

// Exercise the real inline Blade handlers, with controllable request/render promises.
// This catches the old swallowed errors and unawaited ApexCharts rendering.
const dashboardScript = readFileSync(new URL('../../resources/views/script.blade.php', import.meta.url), 'utf8');
const handlers = [...dashboardScript.matchAll(/const (handle_\w+) = async \(\) => \{[\s\S]*?\n    \};/g)];
assert.equal(handlers.length, 7);

for (const [source, name] of handlers) {
    const requestName = source.match(/const datas = await (\w+)\(\)/)[1];
    const builderName = source.match(/const options = (\w+)\(datas\)/)[1];
    const renderName = source.match(/(Render\w+DashboardChart)\(/)[1];
    const createHandler = new Function(requestName, builderName, renderName, `${source}\nreturn ${name};`);

    test(`${name} waits for its chart before completing`, async () => {
        const chart = deferred();
        const handler = createHandler(async () => ({}), () => ({}), () => chart.promise);
        let completed = false;
        const load = handler().then(() => { completed = true; });
        await tick();
        assert.equal(completed, false);
        chart.resolve();
        await load;
        assert.equal(completed, true);
    });

    test(`${name} propagates request and rendering failures to the full-page loader`, async () => {
        const requestError = new Error('request failed');
        const renderError = new Error('render failed');
        const failedRequest = createHandler(async () => { throw requestError; }, () => ({}), async () => {});
        const failedRender = createHandler(async () => ({}), () => ({}), async () => { throw renderError; });
        await assert.rejects(failedRequest, error => error === requestError);
        await assert.rejects(failedRender, error => error === renderError);
    });
}
