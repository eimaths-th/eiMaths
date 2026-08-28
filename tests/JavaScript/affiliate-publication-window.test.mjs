import test from 'node:test';
import assert from 'node:assert/strict';
import {
    InitializeAffiliatePublicationWindow,
} from '../../public/js/affiliate-publication-window.js';

class FakeNode {
    constructor({ id = '', checked = false, value = '' } = {}) {
        this.id = id;
        this.checked = checked;
        this.value = value;
        this.disabled = false;
        this.dataset = {};
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
    constructor(toggle, end_field) {
        this.toggle = toggle;
        this.end_field = end_field;
    }

    querySelectorAll(selector) {
        return selector === '[data-affiliate-publication-unlimited-toggle]'
            ? [this.toggle]
            : [];
    }

    getElementById(id) {
        return id === this.end_field.id ? this.end_field : null;
    }
}

function CreatePublicationWindow({ unlimited = false, end_value = '2026-08-31T23:59' } = {}) {
    const toggle = new FakeNode({ id: 'publication-unlimited', checked: unlimited });
    toggle.dataset.affiliatePublicationEndField = 'publication-end';
    const end_field = new FakeNode({ id: 'publication-end', value: end_value });

    return {
        toggle,
        end_field,
        root: new FakeRoot(toggle, end_field),
    };
}

test('ClearsAndDisablesPublicationEndWhenUnlimitedIsSelected', () => {
    const form = CreatePublicationWindow({ unlimited: true });

    InitializeAffiliatePublicationWindow({ root: form.root });

    assert.equal(form.end_field.value, '');
    assert.equal(form.end_field.disabled, true);
    assert.equal(form.end_field.attributes['aria-disabled'], 'true');
});

test('EnablesPublicationEndWhenUnlimitedIsDeselected', () => {
    const form = CreatePublicationWindow({ unlimited: true });

    InitializeAffiliatePublicationWindow({ root: form.root });
    form.toggle.checked = false;
    form.toggle.listeners.change();

    assert.equal(form.end_field.disabled, false);
    assert.equal(form.end_field.attributes['aria-disabled'], undefined);
});
