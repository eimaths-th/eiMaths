import test from 'node:test';
import assert from 'node:assert/strict';
import { InitializeAffiliateTutorial, SaveTutorialProgress, TutorialOperations } from '../../public/js/affiliate-tutorial.js';

test('cancelled saves cannot resume navigation even if the server response arrives later', () => {
    const operations = TutorialOperations();
    const advancing = operations.begin();
    operations.cancel();
    assert.equal(advancing.signal.aborted, true);
    assert.equal(operations.current(advancing), false);
    const pause = operations.begin();
    assert.equal(operations.current(advancing), false);
    assert.equal(operations.current(pause), true);
});

test('progress requests contain the configured version and CSRF token, and reject HTTP or malformed responses', async () => {
    const config = { version: 2, progressUrl: '/affiliate/tutorial/progress' };
    const signal = new AbortController().signal;
    let sent;
    const transport = async (url, options) => {
        sent = { url, ...options };
        return { ok: true, json: async () => ({ status: 'paused', step: 'bank' }) };
    };
    assert.deepEqual(await SaveTutorialProgress(config, 'pause', 'bank', 'csrf', signal, transport), { status: 'paused', step: 'bank' });
    assert.deepEqual(JSON.parse(sent.body), { version: 2, action: 'pause', step: 'bank' });
    assert.equal(sent.headers['X-CSRF-TOKEN'], 'csrf');
    assert.equal(sent.credentials, 'same-origin');
    assert.equal(sent.signal, signal);
    await assert.rejects(SaveTutorialProgress(config, 'pause', 'bank', '', signal, async () => ({ ok: false })), /Unable to save/);
    await assert.rejects(SaveTutorialProgress(config, 'pause', 'bank', '', signal, async () => ({ ok: true, json: async () => ({ success: true }) })), /Invalid tutorial/);
});

class Node {
    constructor() {
        this.dataset = {}; this.style = {}; this.listeners = {}; this.nodes = new Map(); this.children = [];
        this.hidden = false; this.disabled = false; this.open = false; this.offsetWidth = 420; this.offsetHeight = 320;
        this.clientWidth = 1280; this.clientHeight = 800; this.textContent = '';
    }
    querySelector(key) { if (!this.nodes.has(key)) this.nodes.set(key, new Node()); return this.nodes.get(key); }
    addEventListener(key, fn) { this.listeners[key] = fn; }
    setAttribute() {}
    removeAttribute() {}
    replaceChildren(...children) { this.children = children; }
    append(child) { this.children.push(child); }
    focus() {}
    showModal() { this.open = true; }
    close() { this.open = false; }
    getClientRects() { return []; }
    click() { this.listeners.click?.({ preventDefault() {} }); }
}

function mount(t) {
    const root = new Node(), dialog = new Node(), launcher = new Node(), zoom = new Node();
    const labels = { progress: ':current / :total', error: 'Save failed', paused: 'Paused', unsaved: 'Closed; not saved',
        completed: 'Completed', starterCompleted: 'Starter completed', next: 'Next', finish: 'Finish' };
    const config = { version: 2, storageKey: 'tour:1', progressUrl: '/affiliate/tutorial/progress', completionStep: 'done', autoStart: true,
        progress: null, labels, chapters: { share: 'Share', income: 'Income', payout: 'Payout', withdraw: 'Withdraw' },
        steps: [{ key: 'welcome', chapter: 'share', url: '/affiliate/dashboard', title: 'Welcome', body: 'Welcome' },
            { key: 'copy', chapter: 'share', url: '/affiliate/dashboard', title: 'Copy', body: 'Copy' },
            { key: 'done', chapter: 'withdraw', url: '/affiliate/dashboard', title: 'Done', body: 'Done' }],
        starterSteps: [
            { key: 'copy', chapter: 'share', url: '/affiliate/dashboard', title: 'Copy', body: 'Copy' },
            { key: 'commission', chapter: 'income', url: '/affiliate/dashboard', title: 'Commission', body: 'Commission' },
            { key: 'promptpay', chapter: 'payout', url: '/affiliate/dashboard', title: 'Payout', body: 'Payout' },
            { key: 'withdraw', chapter: 'withdraw', url: '/affiliate/dashboard', title: 'Withdraw', body: 'Withdraw' },
        ] };
    root.getElementById = id => ({ 'affiliate-tutorial': dialog, 'affiliate-tutorial-example': zoom,
        'affiliate-tutorial-config': { textContent: JSON.stringify(config) } }[id] ?? null);
    root.querySelectorAll = () => [launcher]; root.createElement = () => new Node();
    const storage = new Map(), requests = [], navigations = [];
    const globals = {
        sessionStorage: { getItem: key => storage.get(key) ?? null, setItem: (key, value) => storage.set(key, value), removeItem: key => storage.delete(key) },
        window: { setTimeout: () => 1, clearTimeout() {}, addEventListener() {}, scrollBy() {} },
        location: { href: 'http://localhost/affiliate/dashboard', pathname: '/affiliate/dashboard', origin: 'http://localhost', assign: url => navigations.push(url) },
        ResizeObserver: class { observe() {} }, requestAnimationFrame: callback => { callback(); return 1; }, cancelAnimationFrame() {},
        fetch: (url, options) => new Promise((resolve, reject) => requests.push({ resolve, reject, ...options })),
    };
    const previous = new Map(Object.keys(globals).map(key => [key, Object.getOwnPropertyDescriptor(globalThis, key)]));
    for (const [key, value] of Object.entries(globals)) Object.defineProperty(globalThis, key, { configurable: true, writable: true, value });
    t.after(() => { for (const [key, descriptor] of previous) { if (descriptor) Object.defineProperty(globalThis, key, descriptor); else delete globalThis[key]; } });
    InitializeAffiliateTutorial(root);
    return { dialog, launcher, requests, navigations, storage, get: key => dialog.querySelector(`[data-tour-${key}]`) };
}

const flush = () => new Promise(resolve => setImmediate(resolve));
const response = (status, step) => ({ ok: true, json: async () => ({ status, step }) });

test('Pause closes immediately during a save and a late success cannot change the page', async t => {
    const view = mount(t);
    assert.equal(view.dialog.open, true);
    view.get('next').click();
    assert.equal(view.get('next').disabled, true);
    assert.equal(view.get('close').disabled, false);
    assert.equal(view.get('skip').disabled, false);
    view.get('close').click();
    assert.equal(view.dialog.open, false);
    assert.equal(view.requests[0].signal.aborted, true);
    view.requests[0].resolve(response('in_progress', 'commission'));
    view.requests[1].resolve(response('paused', 'copy'));
    await flush();
    assert.deepEqual(view.navigations, []);
    assert.equal(JSON.parse(view.storage.get('tour:1')).status, 'paused');
});

test('A failed save permits retry and Escape still closes; failed pause retains a local resume point', async t => {
    const view = mount(t);
    view.get('next').click();
    view.requests[0].reject(new Error('offline'));
    await flush();
    assert.equal(view.get('next').disabled, false);
    assert.equal(view.get('error').hidden, false);
    view.dialog.listeners.cancel({ preventDefault() {} });
    assert.equal(view.dialog.open, false);
    view.requests[1].reject(new Error('offline'));
    await flush();
    view.launcher.click();
    assert.equal(view.dialog.open, true);
    assert.equal(JSON.parse(view.storage.get('tour:1')).step, 'copy');
});

test('the four-step onboarding completes through the canonical done step', async t => {
    const view = mount(t);
    assert.equal(view.get('progress').textContent, '1 / 4');

    for (const step of ['commission', 'promptpay', 'withdraw']) {
        view.get('next').click();
        const request = view.requests.at(-1);
        assert.equal(JSON.parse(request.body).step, step);
        request.resolve(response('in_progress', step));
        await flush();
    }

    assert.equal(view.get('progress').textContent, '4 / 4');
    view.get('next').click();
    const request = view.requests.at(-1);
    assert.deepEqual(JSON.parse(request.body), { version: 2, action: 'finish', step: 'done' });
    request.resolve(response('completed', 'done'));
    await flush();
    assert.equal(view.dialog.open, false);
});

test('pressing Tutorial manually selects the full walkthrough', t => {
    const view = mount(t);
    assert.equal(view.get('progress').textContent, '1 / 4');
    view.dialog.close();
    view.launcher.click();
    assert.equal(view.dialog.open, true);
    assert.equal(view.get('progress').textContent, '1 / 3');
});
