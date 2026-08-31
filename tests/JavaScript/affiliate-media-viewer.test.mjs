import test from 'node:test';
import assert from 'node:assert/strict';
import { InitializeAffiliateMediaViewer } from '../../public/js/affiliate-media-library.js';

class FakeElement {
    constructor({ dataset = {}, tag_name = 'div', owner_document = null } = {}) {
        this.dataset = dataset;
        this.tagName = tag_name;
        this.ownerDocument = owner_document;
        this.children = [];
        this.attributes = {};
        this.listeners = {};
        this.textContent = '';
        this.focus_count = 0;
    }

    addEventListener(name, listener) {
        this.listeners[name] = listener;
    }

    append(child) {
        this.children.push(child);
    }

    replaceChildren(...children) {
        this.children = children;
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
        return this.query_selector_map?.get(selector) ?? null;
    }
}

class FakeDialog extends FakeElement {
    constructor(options) {
        super(options);
        this.open = false;
    }

    showModal() {
        this.open = true;
    }

    close() {
        this.open = false;
        this.listeners.close?.({ target: this });
    }
}

class FakeDocument {
    createElement(tag_name) {
        return new FakeElement({ tag_name, owner_document: this });
    }
}

class FakeRoot extends FakeElement {
    constructor(dialog, triggers, owner_document) {
        super({ owner_document });
        this.dialog = dialog;
        this.triggers = triggers;
    }

    querySelector(selector) {
        return selector === '[data-media-dialog]' ? this.dialog : null;
    }

    querySelectorAll(selector) {
        return selector === '[data-media-view]' ? this.triggers : [];
    }
}

function CreateViewerFixture(dataset) {
    const document_object = new FakeDocument();
    const title = new FakeElement({ owner_document: document_object });
    const content = new FakeElement({ owner_document: document_object });
    const close_button = new FakeElement({ owner_document: document_object });
    const dialog = new FakeDialog({ owner_document: document_object });
    dialog.query_selector_map = new Map([
        ['[data-media-dialog-title]', title],
        ['[data-media-dialog-content]', content],
        ['[data-media-dialog-close]', close_button],
    ]);
    const trigger = new FakeElement({ dataset, owner_document: document_object });
    const root = new FakeRoot(dialog, [trigger], document_object);

    InitializeAffiliateMediaViewer({ Root: root, Document: document_object });

    return { close_button, content, dialog, title, trigger };
}

test('OpensTheSelectedImageInAnAccessibleDialog', () => {
    const fixture = CreateViewerFixture({
        mediaViewUrl: '/uploads/affiliate/image/campaign.webp',
        mediaViewTitle: 'Campaign image',
        mediaViewType: 'image',
    });
    let default_was_prevented = false;

    fixture.trigger.listeners.click({ preventDefault: () => { default_was_prevented = true; } });

    assert.equal(default_was_prevented, true);
    assert.equal(fixture.dialog.open, true);
    assert.equal(fixture.title.textContent, 'Campaign image');
    assert.equal(fixture.content.children.length, 1);
    assert.equal(fixture.content.children[0].tagName, 'img');
    assert.equal(fixture.content.children[0].src, '/uploads/affiliate/image/campaign.webp');
    assert.equal(fixture.content.children[0].alt, 'Campaign image');
    assert.equal(fixture.close_button.focus_count, 1);
});

test('ClosesTheDialogAndRestoresFocusToTheSelectedMedia', () => {
    const fixture = CreateViewerFixture({
        mediaViewUrl: '/uploads/affiliate/image/campaign.webp',
        mediaViewTitle: 'Campaign image',
        mediaViewType: 'image',
    });

    fixture.trigger.listeners.click({ preventDefault: () => {} });
    fixture.close_button.listeners.click();

    assert.equal(fixture.dialog.open, false);
    assert.equal(fixture.trigger.focus_count, 1);
});

test('RendersVideoMediaWithControlsInsideTheDialog', () => {
    const fixture = CreateViewerFixture({
        mediaViewUrl: '/uploads/affiliate/video/campaign.mp4',
        mediaViewTitle: 'Campaign video',
        mediaViewType: 'video',
    });

    fixture.trigger.listeners.click({ preventDefault: () => {} });

    const video = fixture.content.children[0];
    assert.equal(video.tagName, 'video');
    assert.equal(video.src, '/uploads/affiliate/video/campaign.mp4');
    assert.equal(video.controls, true);
    assert.equal(video.preload, 'metadata');
});
