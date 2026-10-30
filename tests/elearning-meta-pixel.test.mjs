import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const template = readFileSync(new URL('../resources/views/layouts/includes/elearning-meta-pixel.blade.php', import.meta.url), 'utf8');
function load({ event = null, registration = false, host = 'app.eimaths-th.com' } = {}) {
    const script = template.match(/<script>([\s\S]*?)<\/script>/)[1]
        .replace('@json($elearningPixelId)', '"1612378803966693"')
        .replace('@json($elearningSuccessEvent)', JSON.stringify(event))
        .replace("@json(request()->routeIs('guest.quiz.register'))", JSON.stringify(registration));
    const requests = [];
    const context = { location: { hostname: host }, document: {
        createElement: () => ({}),
        getElementsByTagName: () => [{ parentNode: { insertBefore: node => requests.push(node.src) } }],
    }};
    context.window = context;
    vm.createContext(context);
    vm.runInContext(script, context);
    return { context, requests, calls: () => Array.from(context.fbq?.queue || [], args => Array.from(args)) };
}

test('public login sends PageView and form opening once with no data payload', () => {
    const { context, calls } = load();
    context.eimathsTrackFreeTrialOpen();
    context.eimathsTrackFreeTrialOpen();
    assert.deepEqual(calls(), [
        ['set', 'autoConfig', false, '1612378803966693'],
        ['init', '1612378803966693'],
        ['trackSingle', '1612378803966693', 'PageView'],
        ['trackSingleCustom', '1612378803966693', 'OpenFreeTrialForm'],
    ]);
});
test('standalone registration records form opening', () => {
    assert.equal(load({ registration: true }).calls().at(-1)[2], 'OpenFreeTrialForm');
});
test('server success uses standard registration and custom login without double counting auto login', () => {
    assert.deepEqual(load({ event: 'CompleteRegistration' }).calls().slice(2), [
        ['trackSingle', '1612378803966693', 'PageView'],
        ['trackSingle', '1612378803966693', 'CompleteRegistration'],
    ]);
    assert.deepEqual(load({ event: 'ELearningLogin' }).calls().at(-1),
        ['trackSingleCustom', '1612378803966693', 'ELearningLogin']);
    assert.equal(load({ event: 'unexpected' }).calls().length, 3);
});
test('local and preview hosts do not load Meta', () => {
    for (const host of ['localhost', 'test-web.eimaths-th.com', 'app.eimaths-th.com.example.org']) {
        assert.deepEqual(load({ host }).requests, []);
    }
});
test('blocked tracking cannot break opening the form', () => {
    const { context } = load();
    context.fbq = () => { throw new Error('Blocked'); };
    assert.doesNotThrow(() => context.eimathsTrackFreeTrialOpen());
});
