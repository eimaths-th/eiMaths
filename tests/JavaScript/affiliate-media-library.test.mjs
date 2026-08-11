import test from 'node:test';
import assert from 'node:assert/strict';
import {
    CopyText,
    InitializeAffiliateMediaLibrary,
    ResolveMediaAction,
    ShareMedia,
} from '../../public/js/affiliate-media-library.js';

class FakeNode {
    constructor({ dataset = {}, text_content = '' } = {}) {
        this.dataset = dataset;
        this.textContent = text_content;
        this.attributes = {};
        this.listeners = {};
        this.disabled = false;
        this.focus_count = 0;
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

    focus() {
        this.focus_count += 1;
    }

    querySelector(selector) {
        return selector === '[data-media-feedback]' ? this.feedback_node ?? null : null;
    }

    closest(selector) {
        return selector === '[data-media-card]' ? this.card ?? this : null;
    }
}

class FakeRoot extends FakeNode {
    constructor(translations) {
        super();
        this.translations = new FakeNode({ text_content: JSON.stringify(translations) });
        this.selections = new Map();
    }

    setSelection(selector, nodes) {
        this.selections.set(selector, nodes);
    }

    querySelector(selector) {
        if (selector === '[data-media-translations]') return this.translations;
        return null;
    }

    querySelectorAll(selector) {
        return this.selections.get(selector) ?? [];
    }
}

function CreateDeferred() {
    let resolve_promise;
    let reject_promise;
    const promise = new Promise((resolve, reject) => {
        resolve_promise = resolve;
        reject_promise = reject;
    });
    return { promise, resolve: resolve_promise, reject: reject_promise };
}

test('CopiesWithTheClipboardApiAndUsesTheInjectedFallbackWhenNeeded', async () => {
    const clipboard_values = [];
    await CopyText('caption', { Navigator: { clipboard: { writeText: async (value) => clipboard_values.push(value) } } });
    assert.deepEqual(clipboard_values, ['caption']);

    const fallback_values = [];
    await CopyText('tracking-url', { Navigator: {}, Fallback: async (value) => fallback_values.push(value) });
    assert.deepEqual(fallback_values, ['tracking-url']);
    await assert.rejects(() => CopyText('missing-fallback', { Navigator: {} }), /Clipboard is unavailable/);
});

test('CopiesThroughTheDocumentFallbackWhenClipboardApiIsUnavailable', async () => {
    let clipboard_target = null;
    let copied_value = null;
    const document_object = {
        body: {
            appendChild: (node) => { clipboard_target = node; },
            removeChild: () => {},
        },
        createElement: (tag_name) => {
            assert.equal(tag_name, 'textarea');
            return {
                value: '',
                style: {},
                setAttribute: () => {},
                select: () => {},
                setSelectionRange: () => {},
            };
        },
        execCommand: (command) => {
            assert.equal(command, 'copy');
            copied_value = clipboard_target?.value ?? null;
            return true;
        },
    };

    await CopyText('tracking-url', { Navigator: {}, Document: document_object });

    assert.equal(copied_value, 'tracking-url');
});

test('UsesTheDocumentCopyPathAndNativeClipboardAsCopyAttempts', async () => {
    let clipboard_target = null;
    let native_copy_called = false;
    const document_object = {
        body: {
            appendChild: (node) => { clipboard_target = node; },
            removeChild: () => {},
        },
        createElement: () => ({
            value: '',
            style: {},
            setAttribute: () => {},
            select: () => {},
            setSelectionRange: () => {},
        }),
        execCommand: (command) => command === 'copy',
    };
    const native_navigator = {
        clipboard: {
            writeText: async () => { native_copy_called = true; },
        },
    };

    await CopyText('tracking-url', { Navigator: native_navigator, Document: document_object });

    assert.equal(clipboard_target?.value, 'tracking-url');
    assert.equal(native_copy_called, true);
});

test('SharesSuccessfullyCancelsQuietlyAndFallsBackToCopy', async () => {
    const shared = [];
    const native_navigator = { share: async (value) => shared.push(value) };
    assert.deepEqual(await ShareMedia({ title: 'Title', caption: 'Caption', url: '/track' }, { Navigator: native_navigator }), { status: 'shared' });
    assert.deepEqual(shared[0], { title: 'Title', text: 'Caption', url: '/track' });

    const cancelled = await ShareMedia({ title: 'Title', caption: 'Caption', url: '/track' }, {
        Navigator: { share: async () => { throw Object.assign(new Error('cancelled'), { name: 'AbortError' }); } },
    });
    assert.deepEqual(cancelled, { status: 'cancelled' });

    const copied = [];
    const fallback = await ShareMedia({ title: 'Title', caption: 'Caption', url: '/track' }, {
        Navigator: { share: async () => { throw new Error('share failed'); } },
        Copy: async (value) => copied.push(value),
    });
    assert.deepEqual(fallback, { status: 'copied' });
    assert.deepEqual(copied, ['/track']);
});

test('ResolvesCaptionAndTrackingLinkActionsFromDataAttributes', () => {
    assert.deepEqual(ResolveMediaAction({ dataset: { mediaCopyCaption: 'Caption' } }, 'caption'), { type: 'copy', value: 'Caption' });
    assert.deepEqual(ResolveMediaAction({ dataset: { mediaCopyLink: '/track' } }, 'link'), { type: 'copy', value: '/track' });
    assert.deepEqual(ResolveMediaAction({ dataset: { mediaTitle: 'Title', mediaCaption: 'Caption', mediaUrl: '/track' } }, 'share'), {
        type: 'share', title: 'Title', caption: 'Caption', url: '/track',
    });
});

test('DisablesOnlyThePressedActionRestoresFocusAndAnnouncesText', async () => {
    const button = new FakeNode({ dataset: { mediaCopyCaption: 'Safe <caption>' } });
    const card = new FakeNode();
    const feedback = new FakeNode();
    card.feedback_node = feedback;
    button.card = card;
    const deferred_copy = CreateDeferred();
    const root = new FakeRoot({ copied: 'Copied in Thai', action_unavailable: 'Unavailable' });
    root.setSelection('[data-media-copy-caption]', [button]);
    root.setSelection('[data-media-copy-link]', []);
    root.setSelection('[data-media-share]', []);

    InitializeAffiliateMediaLibrary({
        Root: root,
        Navigator: { clipboard: { writeText: () => deferred_copy.promise } },
    });
    const click_promise = button.listeners.click();
    await Promise.resolve();
    assert.equal(button.disabled, true);
    assert.equal(button.attributes['aria-busy'], 'true');

    deferred_copy.resolve();
    await click_promise;
    assert.equal(button.disabled, false);
    assert.equal(button.attributes['aria-busy'], undefined);
    assert.equal(button.focus_count, 1);
    assert.equal(feedback.textContent, 'Copied in Thai');
    assert.equal(feedback.attributes['data-media-feedback-state'], 'success');
    assert.equal(feedback.innerHTML, undefined);
});
