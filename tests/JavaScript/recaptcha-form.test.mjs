import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const PARTIAL_TEMPLATE = readFileSync(new URL('../../resources/views/partials/recaptcha.blade.php', import.meta.url), 'utf8');

function RenderScript() {
    const script = PARTIAL_TEMPLATE.match(/<script>([\s\S]*?)<\/script>/);
    assert.ok(script, 'reCAPTCHA form script must exist');
    return script[1].replace(/@json\([\s\S]*?\)\)/, '"site-key"');
}

function CreateHarness(grecaptcha) {
    const handlers = {};
    const window_handlers = {};
    const timers = [];
    const token_input = { value: '' };
    const form = {
        dataset: { recaptchaAction: 'student_login' },
        submit_count: 0,
        addEventListener(event_name, handler) { handlers[event_name] = handler; },
        querySelector() { return token_input; },
        submit() { this.submit_count += 1; },
    };
    const context = {
        window: { addEventListener(event_name, handler) { window_handlers[event_name] = handler; } },
        document: { querySelectorAll() { return [form]; } },
        setTimeout(callback) { timers.push(callback); return timers.length; },
        clearTimeout() {},
        Promise,
    };
    if (grecaptcha) context.grecaptcha = grecaptcha;
    runInNewContext(RenderScript(), context);

    return {
        form,
        token_input,
        timers,
        window_handlers,
        Submit() { handlers.submit({ preventDefault() {} }); },
    };
}

const Settle = () => new Promise((resolve) => setImmediate(resolve));

function WorkingGrecaptcha(token = 'fresh-token') {
    return { ready(callback) { callback(); }, execute() { return Promise.resolve(token); } };
}

test('submits once with the token despite a double click', async () => {
    const harness = CreateHarness(WorkingGrecaptcha());

    harness.Submit();
    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
    assert.equal(harness.token_input.value, 'fresh-token');
});

test('submits without a token when the script is missing', async () => {
    const harness = CreateHarness(null);

    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
    assert.equal(harness.token_input.value, '');
});

test('submits without a token when execute rejects', async () => {
    const harness = CreateHarness({ ready(callback) { callback(); }, execute() { return Promise.reject(new Error('rejected')); } });

    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
});

test('submits without a token when execute throws', async () => {
    const harness = CreateHarness({ ready(callback) { callback(); }, execute() { throw new Error('Invalid site key'); } });

    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
    assert.equal(harness.token_input.value, '');
});

test('submits without a token when execute throws after an asynchronous ready', async () => {
    const harness = CreateHarness({ ready(callback) { setImmediate(callback); }, execute() { throw new Error('Invalid site key'); } });

    harness.Submit();
    await Settle();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
});

test('accepts a plain token value from execute', async () => {
    const harness = CreateHarness({ ready(callback) { callback(); }, execute() { return 'plain-token'; } });

    harness.Submit();
    await Settle();

    assert.equal(harness.token_input.value, 'plain-token');
});

test('keeps the pending guard on the first page show', async () => {
    const harness = CreateHarness({ ready() {} });

    harness.Submit();
    harness.window_handlers.pageshow({ persisted: false });
    harness.Submit();
    harness.timers[0]();
    await Settle();

    assert.equal(harness.timers.length, 1);
    assert.equal(harness.form.submit_count, 1);
});

test('submits without a token when ready throws', async () => {
    const harness = CreateHarness({ ready() { throw new Error('not ready'); } });

    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
});

test('submits without a token when ready never fires', async () => {
    const harness = CreateHarness({ ready() {} });

    harness.Submit();
    await Settle();
    assert.equal(harness.form.submit_count, 0);

    harness.timers[0]();
    await Settle();

    assert.equal(harness.form.submit_count, 1);
    assert.equal(harness.token_input.value, '');
});

test('accepts a token from a plain thenable result', async () => {
    const harness = CreateHarness({ ready(callback) { callback(); }, execute() { return { then(resolve) { resolve('thenable-token'); } }; } });

    harness.Submit();
    await Settle();

    assert.equal(harness.token_input.value, 'thenable-token');
});

test('works again after back navigation restores the page', async () => {
    const harness = CreateHarness(WorkingGrecaptcha());
    harness.Submit();
    await Settle();

    harness.window_handlers.pageshow({ persisted: true });
    assert.equal(harness.token_input.value, '');

    harness.Submit();
    await Settle();

    assert.equal(harness.form.submit_count, 2);
});
