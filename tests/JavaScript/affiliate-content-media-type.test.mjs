import test from 'node:test';
import assert from 'node:assert/strict';
import {
    InitializeAffiliateContentMediaTypeSelector,
} from '../../public/js/affiliate-content-media-type.js';

class FakeNode {
    constructor({ dataset = {}, value = '', files = [], text_content = '' } = {}) {
        this.dataset = dataset;
        this.value = value;
        this.files = files;
        this.textContent = text_content;
        this.attributes = {};
        this.listeners = {};
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
}

class FakeRoot {
    constructor(type_select, file_input, help_text, status) {
        this.nodes = new Map([
            ['[data-affiliate-content-media-type]', type_select],
            ['[data-affiliate-content-media-file]', file_input],
            ['[data-affiliate-content-media-help]', help_text],
            ['[data-affiliate-content-media-status]', status],
        ]);
    }

    querySelector(selector) {
        return this.nodes.get(selector) ?? null;
    }
}

function CreateMediaTypeForm({ media_type = 'image', files = [] } = {}) {
    const type_select = new FakeNode({ value: media_type });
    const file_input = new FakeNode({ files });
    const help_text = new FakeNode({
        dataset: {
            affiliateMediaImageHelpMessage: 'Image files only',
            affiliateMediaVideoHelpMessage: 'Video files only',
        },
    });
    const status = new FakeNode();

    return {
        type_select,
        file_input,
        help_text,
        status,
        root: new FakeRoot(type_select, file_input, help_text, status),
    };
}

test('UpdatesFileAcceptAndHelpTextForTheSelectedMediaType', () => {
    const form = CreateMediaTypeForm();

    InitializeAffiliateContentMediaTypeSelector({ root: form.root });

    assert.equal(form.file_input.attributes.accept, 'image/jpeg,image/png,image/gif,image/webp');
    assert.equal(form.help_text.textContent, 'Image files only');

    form.type_select.value = 'video';
    form.type_select.listeners.change();

    assert.equal(form.file_input.attributes.accept, 'video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv');
    assert.equal(form.help_text.textContent, 'Video files only');
});

test('ClearsAnIncompatibleFileWhenTheMediaTypeChanges', () => {
    const form = CreateMediaTypeForm({
        files: [{ name: 'banner.jpg', type: 'image/jpeg' }],
    });
    form.file_input.value = 'C:\\fakepath\\banner.jpg';

    InitializeAffiliateContentMediaTypeSelector({ root: form.root });

    form.type_select.value = 'video';
    form.type_select.listeners.change();

    assert.equal(form.file_input.value, '');
    assert.equal(form.status.textContent, 'The selected file was cleared because it does not match the selected media type.');
});

test('KeepsACompatibleFileWhenTheSelectedMediaTypeIsApplied', () => {
    const form = CreateMediaTypeForm({
        media_type: 'video',
        files: [{ name: 'lesson.mp4', type: 'video/mp4' }],
    });
    form.file_input.value = 'C:\\fakepath\\lesson.mp4';

    InitializeAffiliateContentMediaTypeSelector({ root: form.root });

    assert.equal(form.file_input.value, 'C:\\fakepath\\lesson.mp4');
    assert.equal(form.status.textContent, '');
});
