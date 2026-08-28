import test from 'node:test';
import assert from 'node:assert/strict';
import {
    InitializeAffiliateCampaignCodeGenerator,
    RequestUniqueCampaignCode,
} from '../../public/js/affiliate-campaign-code-generator.js';

class FakeNode {
    constructor({ dataset = {}, value = '', text_content = '' } = {}) {
        this.dataset = dataset;
        this.value = value;
        this.textContent = text_content;
        this.attributes = {};
        this.listeners = {};
        this.disabled = false;
        this.focus_count = 0;
        this.dispatched_events = [];
    }

    addEventListener(name, listener) {
        this.listeners[name] = listener;
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    removeAttribute(name) {
        delete this.attributes[name];
    }

    dispatchEvent(event) {
        this.dispatched_events.push(event);
    }

    focus() {
        this.focus_count += 1;
    }
}

class FakeRoot {
    constructor(button, input, status) {
        this.nodes = new Map([
            ['[data-affiliate-campaign-code-generate]', button],
            ['[data-affiliate-campaign-code-input]', input],
            ['[data-affiliate-campaign-code-status]', status],
        ]);
    }

    querySelector(selector) {
        return this.nodes.get(selector) ?? null;
    }
}

test('FillsTheCampaignFieldWithAnUnusedServerGeneratedCode', async () => {
    const button = new FakeNode({
        dataset: {
            affiliateCampaignCodeEndpoint: '/affiliate-content-manage/generate-campaign-code',
            affiliateCampaignCodeInputSelector: '[data-affiliate-campaign-code-input]',
            affiliateCampaignCodeStatusSelector: '[data-affiliate-campaign-code-status]',
            affiliateCampaignCodeGeneratingMessage: 'กำลังสุ่มรหัส',
            affiliateCampaignCodeGeneratedMessage: 'สร้างรหัสแล้ว',
            affiliateCampaignCodeGenerationFailedMessage: 'สร้างรหัสไม่สำเร็จ',
        },
    });
    const input = new FakeNode();
    const status = new FakeNode();
    const root = new FakeRoot(button, input, status);
    const fetch_calls = [];

    InitializeAffiliateCampaignCodeGenerator({
        Root: root,
        Fetch: async (endpoint, options) => {
            fetch_calls.push({ endpoint, options });
            return {
                ok: true,
                status: 200,
                json: async () => ({ campaign_code: 'cmp-freshcode123' }),
            };
        },
    });

    await button.listeners.click();

    assert.equal(input.value, 'cmp-freshcode123');
    assert.equal(input.dispatched_events.length, 1);
    assert.equal(button.disabled, false);
    assert.equal(button.attributes['aria-busy'], undefined);
    assert.equal(status.textContent, 'สร้างรหัสแล้ว');
    assert.equal(status.attributes['data-affiliate-campaign-code-state'], 'success');
    assert.equal(input.focus_count, 1);
    assert.deepEqual(fetch_calls, [{
        endpoint: '/affiliate-content-manage/generate-campaign-code',
        options: {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    }]);
});

test('RestoresTheGenerateButtonAndAnnouncesAnErrorWhenGenerationFails', async () => {
    const button = new FakeNode({
        dataset: {
            affiliateCampaignCodeEndpoint: '/affiliate-content-manage/generate-campaign-code',
            affiliateCampaignCodeInputSelector: '[data-affiliate-campaign-code-input]',
            affiliateCampaignCodeStatusSelector: '[data-affiliate-campaign-code-status]',
            affiliateCampaignCodeGeneratingMessage: 'กำลังสุ่มรหัส',
            affiliateCampaignCodeGeneratedMessage: 'สร้างรหัสแล้ว',
            affiliateCampaignCodeGenerationFailedMessage: 'สร้างรหัสไม่สำเร็จ',
        },
    });
    const input = new FakeNode({ value: 'manual-code' });
    const status = new FakeNode();
    const root = new FakeRoot(button, input, status);

    InitializeAffiliateCampaignCodeGenerator({
        Root: root,
        Fetch: async () => ({ ok: false, status: 503, json: async () => ({}) }),
    });

    await button.listeners.click();

    assert.equal(input.value, 'manual-code');
    assert.equal(button.disabled, false);
    assert.equal(button.attributes['aria-busy'], undefined);
    assert.equal(status.textContent, 'สร้างรหัสไม่สำเร็จ');
    assert.equal(status.attributes['data-affiliate-campaign-code-state'], 'error');
});

test('RejectsAnInvalidGeneratedCampaignCodePayload', async () => {
    await assert.rejects(
        () => RequestUniqueCampaignCode('/affiliate-content-manage/generate-campaign-code', {
            Fetch: async () => ({ ok: true, json: async () => ({ campaign_code: 'Bad Code' }) }),
        }),
        /Invalid campaign code response/,
    );
});
