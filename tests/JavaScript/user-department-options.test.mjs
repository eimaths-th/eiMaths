import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import vm from 'node:vm';

const scriptPath = process.env.USER_SCRIPT_PATH
    ? resolve(process.env.USER_SCRIPT_PATH)
    : new URL('../../resources/views/users/script.blade.php', import.meta.url);
const ui = {
    messages: { confirm_save: 'Save?', department_load_failed: 'Departments unavailable', user_load_failed: 'User unavailable' },
    users: { modal: { select: 'Please select' } },
    data_table: {},
};
// Only Blade substitutions are stubbed; execute the real page's loader and handlers.
const source = readFileSync(scriptPath, 'utf8')
    .replace(/^\s*<script>|<\/script>\s*$/g, '')
    .replace("@json(__('user-access'))", JSON.stringify(ui))
    .replace(/@if \(!empty\(\$showReferralColumn\)\)[\s\S]*?@endif/g, '')
    .replace(/{{ !empty\(\$showReferralColumn\) \? 2 : 1 }}/g, '1')
    .replace(/{{ route\('departments.find', [\s\S]*?}}/g, '/departments/find/add/__centre__')
    .replace(/{{ route\('users.edit', ':id'\) }}/g, '/users/:id/edit')
    .replace(/{{ route\('users.save', ':id'\) }}/g, '/users/save/:id')
    .replace(/{{ route\('users.store'\) }}/g, '/users');
assert.ok(!source.includes('{{') && !source.includes('@json'), 'all Blade expressions are rendered for the JS harness');

function harness() {
    const elements = new Map(), requests = [], messages = [], events = [];
    const document = {};
    const table = {
        ajax: { reload() {} },
        buttons: () => ({ container: () => ({ appendTo() {} }) }),
        row: () => ({ remove: () => ({ draw() {} }) }),
    };
    const multipleSelects = new Set(['#AddDepartment', '#EditDepartment', '#AddPosition', '#EditPosition']);
    class Element {
        constructor(selector) {
            this.selector = selector;
            this.value = null;
            this.markup = '';
            this.props = {};
            this.dataset = {};
            this.handlers = [];
            this.modals = [];
        }
        ready(fn) { fn(); return this; }
        on(event, selector, callback) {
            this.handlers.push({ event, selector: typeof selector === 'string' ? selector : null, callback: callback || selector });
            return this;
        }
        click(fn) { return this.on('click', fn); }
        change(fn) { return fn ? this.on('change', fn) : this.trigger('change'); }
        trigger(event) {
            events.push([this.selector, event]);
            for (const handler of this.handlers) {
                if (event.includes('.') ? handler.event === event : handler.event.split('.')[0] === event) {
                    handler.callback.call(this, { preventDefault() {} });
                }
            }
            return this;
        }
        val(value) {
            if (arguments.length === 0) return this.value;
            this.value = value === null || value === undefined ? null
                : multipleSelects.has(this.selector) ? (Array.isArray(value) ? value : [String(value)]) : value;
            return this;
        }
        html(value) { if (arguments.length === 0) return this.markup; this.markup = value; return this; }
        text(value) {
            this.markup = String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return this;
        }
        empty() { this.markup = ''; this.value = null; return this; }
        prop(name, value) { if (arguments.length === 1) return this.props[name]; this.props[name] = value; return this; }
        data(name, value) { if (arguments.length === 1) return this.dataset[name]; this.dataset[name] = value; return this; }
        is() { return Boolean(this.props.checked); }
        attr() { return 'test-token'; }
        select2() { return this; }
        DataTable() { return table; }
        modal(action) { this.modals.push(action); return this; }
        append(value) { this.markup += value; return this; }
        hide() { return this; }
        show() { return this; }
    }
    function $(selector) {
        if (selector instanceof Element) return selector;
        if (selector === '<div>') return new Element(selector);
        if (!elements.has(selector)) elements.set(selector, new Element(selector));
        return elements.get(selector);
    }
    $.ajax = options => { requests.push(options); return {}; };
    $.ajaxSetup = () => {};
    $.each = (entries, callback) => Object.entries(entries).forEach(([key, value]) => callback(key, value));
    vm.runInNewContext(source, {
        $, document, URLSearchParams, window: { location: { search: '' } },
        confirm: () => true, alert() {}, console,
        toastr: { error: message => messages.push(message), success() {} },
    });
    function click(selector, id) {
        const target = $(selector);
        if (id !== undefined) target.data('id', id);
        target.trigger('click');
        $(document).handlers.filter(handler => handler.event === 'click' && handler.selector === selector)
            .forEach(handler => handler.callback.call(target, { preventDefault() {} }));
    }
    function centre(form, value) { $(`#${form}Department`).val(value).trigger('change'); }
    return { $, requests, messages, events, click, centre };
}

const options = (...names) => names.map((name, index) => `<option value="${index + 1}">${name}</option>`).join('');
const userResponse = (id, centre, position = null, markup = options('Admin', 'Operations')) => ({
    data: { id, department_id: centre, position_id: position, name: `User ${id}`, email: `user${id}@example.test`, disable: 0 },
    html: '<option value="BranchAdmin" selected>BranchAdmin</option>',
    select_list_position: markup,
});

test('opening Edit loads all fresh department options without requiring a centre reselection', () => {
    const h = harness();
    h.$('#EditPassword').val('previous password');
    h.$('#EditPasswordC').val('previous password');
    h.click('#getEditData', 84);
    assert.equal(h.requests.length, 1);
    assert.equal(h.requests[0].cache, false);
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), true);
    assert.equal(h.$('#EditPassword').val(), '');
    assert.equal(h.$('#EditPasswordC').val(), '');
    h.requests[0].success(userResponse(84, 12));
    assert.equal(h.requests.length, 1, 'programmatic Centre selection must not start another lookup');
    assert.equal(h.$('#EditPosition').html(), options('Admin', 'Operations'));
    assert.equal(h.$('#EditPosition').val(), null);
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), false);
    assert.deepEqual(h.$('#EditModal').modals, ['show']);

    h.click('#getEditData', 84);
    h.requests[1].success(userResponse(84, 12, 2, options('Admin', 'Operations', 'New department')));
    assert.equal(h.$('#EditPosition').html(), options('Admin', 'Operations', 'New department'));
    assert.deepEqual(h.$('#EditPosition').val(), ['2']);
});

test('creating or changing a centre only updates the matching modal and waits before saving', () => {
    const h = harness();
    h.$('#EditPosition').html('Existing edit options').val(['9']);
    h.centre('Add', ['12']);
    const request = h.requests[0];
    assert.equal(request.url, '/departments/find/add/12');
    assert.equal(request.cache, false);
    assert.notEqual(request.async, false);
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
    h.click('#SubmitCreateForm');
    assert.equal(h.requests.length, 1, 'loading must block even a programmatically dispatched Save');
    request.success({ html: options('New department') });
    assert.equal(h.$('#AddPosition').html(), options('New department'));
    assert.equal(h.$('#EditPosition').html(), 'Existing edit options');
    assert.deepEqual(h.$('#EditPosition').val(), ['9']);
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
});

test('slow responses from an earlier centre never replace current options or release Save', () => {
    const h = harness();
    h.centre('Add', ['12']);
    h.centre('Add', ['13']);
    h.requests[0].success({ html: options('Old centre') });
    assert.equal(h.$('#AddPosition').html(), '');
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
    h.requests[1].success({ html: options('Current centre') });
    h.requests[0].error({ responseJSON: { message: 'Stale error' } });
    assert.equal(h.$('#AddPosition').html(), options('Current centre'));
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
    assert.deepEqual(h.messages, []);
});

test('an old lookup cannot overwrite options from a newly opened user', () => {
    const h = harness();
    h.click('#getEditData', 84);
    h.requests[0].success(userResponse(84, 12));
    h.centre('Edit', ['13']);
    h.click('#getEditData', 85);
    h.requests[2].success(userResponse(85, 14, 1, options('Latest user department')));
    h.requests[1].success({ html: options('Old lookup department') });
    assert.equal(h.$('#EditPosition').html(), options('Latest user department'));
    assert.deepEqual(h.$('#EditPosition').val(), ['1']);
    assert.equal(h.$('#editName').val(), 'User 85');
});

test('out of order edit responses cannot restore the previous user or allow saving before current load', () => {
    const h = harness();
    h.click('#getEditData', 84);
    h.click('#getEditData', 85);
    h.requests[0].success(userResponse(84, 12));
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), true);
    assert.equal(h.$('#editName').val(), null);
    h.requests[1].success(userResponse(85, 14));
    h.requests[0].error({ responseJSON: { message: 'Old error' } });
    assert.equal(h.$('#editName').val(), 'User 85');
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), false);
    assert.deepEqual(h.messages, []);
});

for (const value of [null, []]) {
    test(`clearing a centre with ${value === null ? 'null' : 'an empty array'} invalidates pending lookups safely`, () => {
        const h = harness();
        h.centre('Add', ['12']);
        h.centre('Add', value);
        assert.equal(h.requests.length, 1);
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
        h.requests[0].success({ html: options('Outdated department') });
        assert.equal(h.$('#AddPosition').html(), '');
    });
}

test('lookup failure blocks saving, escapes the error, and reopening Create retries with the current centre', () => {
    const h = harness();
    h.$('#EditPosition').html('Do not reset').val(['8']);
    h.centre('Add', ['12']);
    h.requests[0].error({ responseJSON: { message: '<img src=x onerror=alert(1)>' } });
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
    assert.equal(h.messages[0], '&lt;img src=x onerror=alert(1)&gt;');
    h.click('#SubmitCreateForm');
    assert.equal(h.requests.length, 1);
    h.click('#CreateButton');
    assert.equal(h.requests.length, 2);
    assert.equal(h.requests[1].url, '/departments/find/add/12');
    assert.equal(h.$('#EditPosition').html(), 'Do not reset');
    assert.ok(!h.events.some(([selector, event]) => selector === '.form' && event === 'reset'));
    h.requests[1].success({ html: options('Available now') });
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
});

test('failed Edit lookup keeps Save blocked and succeeds on a fresh reopen', () => {
    const h = harness();
    h.click('#getEditData', 84);
    h.requests[0].error({});
    h.click('#SubmitEditForm');
    assert.equal(h.requests.length, 1);
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), true);
    assert.deepEqual(h.messages, ['User unavailable']);
    h.click('#getEditData', 84);
    h.requests[1].success(userResponse(84, 12));
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), false);
});

test('create and update send an explicit null department when no position is selected', () => {
    const h = harness();
    h.$('#AddDepartment').val(['12']);
    h.$('#AddPosition').val(null);
    h.click('#SubmitCreateForm');
    assert.equal(h.requests[0].data.position_id, null);
    assert.equal(h.requests[0].data.department_id, '12');

    h.click('#getEditData', 84);
    h.requests[1].success(userResponse(84, 12));
    h.$('#EditPosition').val([]);
    h.click('#SubmitEditForm');
    assert.equal(h.requests[2].data.position, null);
    assert.equal(h.requests[2].data.department, '12');
    assert.equal(h.requests[2].url, '/users/save/84');
});
