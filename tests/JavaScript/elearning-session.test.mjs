import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const QUIZ_TEMPLATE = readFileSync(new URL('../../resources/views/home/quiz-show.blade.php', import.meta.url), 'utf8');
const REPLACEMENT = { code: 'ELEARNING_SESSION_REPLACED', login_url: '/students-login' };

function RenderScript(marker) {
    const script = [...QUIZ_TEMPLATE.matchAll(/<script>([\s\S]*?)<\/script>/g)]
        .map((match) => match[1]).find((source) => source.includes(marker));
    assert.ok(script, `Quiz script for ${marker} must exist`);
    return script.replace(/\{\{[\s\S]*?\}\}/g, (expression) => {
        if (expression.includes('quiz.show')) return '/quiz/__QUIZ__?currentQuestionIndex=__INDEX__';
        if (expression.includes('quiz.saveAnswer')) return '/quiz/save-answer';
        if (expression.includes('check.answer')) return '/check-answer';
        return '1';
    });
}

async function RunSave(response) {
    let click_handler;
    let request_options;
    const errors = [];
    const window = { location: { href: '/quiz/session-test-quiz' } };
    const button = {
        addEventListener(event_name, handler) { click_handler = handler; },
        getAttribute() { return '1'; },
    };
    runInNewContext(RenderScript('function saveAnswer'), {
        window,
        document: {
            addEventListener(event_name, handler) { handler(); },
            getElementById() { return {}; },
            querySelectorAll() { return [button]; },
            querySelector() { return { value: 'csrf-test' }; },
        },
        FormData: class { append() {} },
        fetch: async (url, options) => { request_options = options; return response; },
        console: { log() {}, error(...arguments_list) { errors.push(arguments_list); } },
    });
    click_handler.call(button, { preventDefault() {} });
    await new Promise((resolve) => setImmediate(resolve));
    return { window, request_options, errors };
}

test('replaced answer-save session navigates to login without continuing quiz navigation', async () => {
    const result = await RunSave({ status: 401, ok: false, json: async () => REPLACEMENT });
    assert.equal(result.window.location.href, '/students-login');
    assert.equal(result.request_options.headers.Accept, 'application/json');
    assert.deepEqual(result.errors, []);
});

test('successful answer save keeps next-question navigation', async () => {
    const result = await RunSave({ status: 200, ok: true, json: async () => ({
        changeToIndex: 1, quiz: { slug: 'session-test-quiz' }, quizStartTime: 100,
    }) });
    assert.equal(result.window.location.href, '/quiz/session-test-quiz?currentQuestionIndex=1');
    assert.deepEqual(result.errors, []);
});

function RunAnswerCheck(error_response) {
    const window = { location: { href: '/quiz/session-test-quiz' } };
    let click_handler;
    const element = {
        ready(handler) { handler(); }, hide() {},
        click(handler) { click_handler = handler; },
        val() { return 'written'; },
    };
    const jquery = () => element;
    jquery.ajax = (options) => options.error(error_response, 'error', 'Unauthorized');
    runInNewContext(RenderScript('$.ajax({'), {
        window, $: jquery, Date, setInterval() {},
        document: {
            addEventListener(event_name, handler) { handler(); },
            getElementById() { return {}; },
        },
        console: { error() {} },
    });
    click_handler();
    return window.location.href;
}

test('replaced answer-check session navigates to login', () => {
    assert.equal(RunAnswerCheck({ status: 401, responseJSON: REPLACEMENT }), '/students-login');
});

test('unrelated answer-check errors do not redirect to login', () => {
    assert.equal(RunAnswerCheck({ status: 500 }), '/quiz/session-test-quiz');
});
