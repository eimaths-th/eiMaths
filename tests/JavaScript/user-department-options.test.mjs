import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import vm from 'node:vm';

const scriptPath = process.env.USER_SCRIPT_PATH
    ? resolve(process.env.USER_SCRIPT_PATH)
    : new URL('../../resources/views/users/script.blade.php', import.meta.url);
const ui = {
    messages: { confirm_save: 'Save?', department_load_failed: 'Departments unavailable', user_load_failed: 'User unavailable', role_load_failed: 'Roles unavailable', no_assignable_roles: 'No assignable roles', assignable_roles_hint: 'Choose an allowed role for this centre' },
    users: { modal: { select: 'Please select' } },
    data_table: {},
};
// Executes the real handlers with a lightweight transport/coordination harness.
// DOM/widget integration is covered by user-department-select2.fixture.mjs using real jQuery and Select2.
const source = readFileSync(scriptPath, 'utf8')
    .replace(/^\s*<script>|<\/script>\s*$/g, '')
    .replace("@json(__('user-access'))", JSON.stringify(ui))
    .replace(/@if \(!empty\(\$showReferralColumn\)\)[\s\S]*?@endif/g, '')
    .replace(/{{ !empty\(\$showReferralColumn\) \? 2 : 1 }}/g, '1')
    .replace(/{{ route\('departments.find', [\s\S]*?}}/g, '/departments/find/add/__centre__')
    .replace(/{{ route\('users.edit', ':id'\) }}/g, '/users/:id/edit')
    .replace(/{{ route\('users.save', ':id'\) }}/g, '/users/save/:id')
    .replace(/{{ route\('users.store'\) }}/g, '/users')
    .replace(/{{ route\('users.role-options'\) }}/g, '/users/role-options');
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
    const escape = value => String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    class Option {
        constructor(text, value) { this.text = text; this.value = value; }
        toString() { return `<option value="${escape(this.value)}">${escape(this.text)}</option>`; }
    }
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
            if (multipleSelects.has(this.selector)) {
                // jQuery val(null) normalizes to '', selecting a real blank option if present.
                // val([]) actually clears every option on a multiple select.
                this.value = value === null || value === undefined
                    ? (/<option\b[^>]*\bvalue\s*=\s*["']{2}/i.test(this.markup) ? [''] : [])
                    : (Array.isArray(value) ? Array.from(value, String) : [String(value)]);
            } else {
                this.value = value === null || value === undefined ? null : value;
            }
            return this;
        }
        html(value) {
            if (arguments.length === 0) return this.markup;
            this.markup = value;
            if (this.selector === '#AddRole' || this.selector === '#editRole') {
                const options = this.prop('options');
                this.value = (options.find(option => option.hasAttribute('selected')) || options[0])?.value ?? null;
            }
            return this;
        }
        text(value) {
            this.markup = String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return this;
        }
        empty() { this.markup = ''; this.value = null; return this; }
        prop(name, value) {
            if (arguments.length === 1) {
                if (name === 'options') return Array.from(this.markup.matchAll(/<option\b([^>]*)>(.*?)<\/option>/g), match => ({
                    value: match[1].match(/value=["']([^"']*)["']/)?.[1] || '',
                    hasAttribute: attribute => new RegExp('\\b' + attribute + '(?:\\s|=|$)').test(match[1]),
                }));
                return this.props[name];
            }
            this.props[name] = value; return this;
        }
        data(name, value) { if (arguments.length === 1) return this.dataset[name]; this.dataset[name] = value; return this; }
        is() { return Boolean(this.props.checked); }
        attr(name, value) { return arguments.length > 1 ? this : 'test-token'; }
        after() { return this; }
        prepend(value) { this.markup = String(value) + this.markup; return this; }
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
        $, document, Option, URLSearchParams, window: { location: { search: '' } },
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

const options = (...names) => '<option value="">No department</option>'
    + names.map((name, index) => `<option value="${index + 1}">${name}</option>`).join('');
const userResponse = (id, centre, position = null, markup = options('Admin', 'Operations')) => ({
    data: { id, department_id: centre, position_id: position, name: `User ${id}`, email: `user${id}@example.test`, disable: 0 },
    html: '<option value="BranchAdmin" selected>BranchAdmin</option>',
    select_list_position: markup,
});

const roleResponse = (...names) => ({ roles: names.map(name => ({ value: name, label: name })) });
const lastLookup = (h, kind) => h.requests.findLast(request => kind === 'roles'
    ? request.url === '/users/role-options' : request.url.startsWith('/departments/find/'));
function resolveCreate(h, role = 'Teacher') {
    lastLookup(h, 'departments').success({ html: options('New department') });
    lastLookup(h, 'roles').success(roleResponse(role));
    h.$('#AddRole').val(role).trigger('change');
}

test('opening Edit loads fresh departments and keeps the existing role without another lookup', () => {
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
    assert.equal(h.requests.length, 1);
    assert.equal(h.$('#EditPosition').html(), options('Admin', 'Operations'));
    assert.deepEqual(h.$('#EditPosition').val(), []);
    assert.equal(h.$('#editRole').val(), 'BranchAdmin');
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), false);
    h.click('#getEditData', 84);
    h.requests[1].success(userResponse(84, 12, 2, options('Admin', 'Operations', 'New department')));
    assert.deepEqual(h.$('#EditPosition').val(), ['2']);
    assert.match(h.$('#EditPosition').html(), /New department/);
});

for (const first of ['departments', 'roles']) {
    test(`create waits for both lookups and explicit role choice when ${first} loads first`, () => {
        const h = harness();
        h.$('#EditPosition').html('Existing edit options').val(['9']);
        h.centre('Add', ['12']);
        assert.equal(h.requests.length, 2);
        assert.equal(lastLookup(h, 'roles').data.department_id, '12');
        assert.equal(lastLookup(h, 'roles').cache, false);
        assert.equal(h.$('#AddRole').val(), '');
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
        const departmentRequest = lastLookup(h, 'departments');
        const roleRequest = lastLookup(h, 'roles');
        if (first === 'departments') departmentRequest.success({ html: options('New department') });
        else { roleRequest.success(roleResponse('Teacher')); h.$('#AddRole').val('Teacher').trigger('change'); }
        h.click('#SubmitCreateForm');
        assert.equal(h.requests.length, 2);
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
        if (first === 'roles') departmentRequest.success({ html: options('New department') });
        else {
            roleRequest.success(roleResponse('Teacher'));
            assert.equal(h.$('#AddRole').val(), '', 'Loading must never silently assign a role');
            assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
            h.$('#AddRole').val('Teacher').trigger('change');
        }
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
        assert.equal(h.$('#EditPosition').html(), 'Existing edit options');
        assert.deepEqual(h.$('#EditPosition').val(), ['9']);
    });
}

test('stale department and role responses cannot restore choices from the previous centre', () => {
    const h = harness();
    h.centre('Add', ['12']);
    const oldDepartment = lastLookup(h, 'departments'), oldRole = lastLookup(h, 'roles');
    h.centre('Add', ['13']);
    oldDepartment.success({ html: options('Old centre') });
    oldRole.success(roleResponse('SystemAdmin'));
    assert.equal(h.$('#AddPosition').html(), '');
    assert.doesNotMatch(h.$('#AddRole').html(), /SystemAdmin/);
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
    resolveCreate(h);
    oldDepartment.error({ responseJSON: { message: 'Stale error' } });
    oldRole.error({ responseJSON: { message: 'Stale role error' } });
    assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
    assert.deepEqual(h.messages, []);
});

test('opening a different user invalidates pending department and role lookups from old edit', () => {
    const h = harness();
    h.click('#getEditData', 84);
    h.requests[0].success(userResponse(84, 12));
    h.centre('Edit', ['13']);
    const oldDepartment = lastLookup(h, 'departments'), oldRole = lastLookup(h, 'roles');
    assert.equal(h.$('#editRole').val(), '');
    h.click('#getEditData', 85);
    h.requests.at(-1).success(userResponse(85, 14, 1, options('Latest user department')));
    oldDepartment.success({ html: options('Old lookup department') });
    oldRole.success(roleResponse('WrongRole'));
    assert.equal(h.$('#EditPosition').html(), options('Latest user department'));
    assert.deepEqual(h.$('#EditPosition').val(), ['1']);
    assert.equal(h.$('#editName').val(), 'User 85');
    assert.equal(h.$('#editRole').val(), 'BranchAdmin');
    assert.doesNotMatch(h.$('#editRole').html(), /WrongRole/);
});

test('out of order edit responses cannot restore the previous user or release Save early', () => {
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
    test(`clearing a centre with ${value === null ? 'null' : 'an empty array'} clears roles and invalidates pending results`, () => {
        const h = harness();
        h.centre('Add', ['12']);
        const department = lastLookup(h, 'departments'), roles = lastLookup(h, 'roles');
        h.centre('Add', value);
        assert.equal(h.requests.length, 2);
        department.success({ html: options('Outdated department') });
        roles.success(roleResponse('Teacher'));
        assert.equal(h.$('#AddPosition').html(), '');
        assert.equal(h.$('#AddRole').val(), '');
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
    });
}

for (const kind of ['departments', 'roles']) {
    test(`${kind} lookup failure keeps Save blocked and fresh Create reopening retries both`, () => {
        const h = harness();
        h.$('#EditPosition').html('Do not reset').val(['8']);
        h.centre('Add', ['12']);
        lastLookup(h, kind).error({ responseJSON: { message: '<img src=x onerror=alert(1)>' } });
        if (kind === 'departments') { lastLookup(h, 'roles').success(roleResponse('Teacher')); h.$('#AddRole').val('Teacher').trigger('change'); }
        else lastLookup(h, 'departments').success({ html: options('Admin') });
        h.click('#SubmitCreateForm');
        assert.equal(h.requests.length, 2);
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), true);
        assert.equal(h.messages[0], '&lt;img src=x onerror=alert(1)&gt;');
        h.click('#CreateButton');
        assert.equal(h.requests.length, 4);
        assert.equal(lastLookup(h, 'roles').data.department_id, '12');
        assert.equal(h.$('#EditPosition').html(), 'Do not reset');
        resolveCreate(h);
        assert.equal(h.$('#SubmitCreateForm').prop('disabled'), false);
    });
}

test('empty assignable roles show explanation and cannot be submitted even after departments succeed', () => {
    const h = harness();
    h.centre('Add', ['12']);
    lastLookup(h, 'roles').success(roleResponse());
    lastLookup(h, 'departments').success({ html: options('Admin') });
    assert.equal(h.$('#AddRoleHelp').html(), 'No assignable roles');
    assert.equal(h.$('#AddRole').prop('disabled'), true);
    h.click('#SubmitCreateForm');
    assert.equal(h.requests.length, 2);
});

test('role labels are inserted as text instead of executable HTML', () => {
    const h = harness();
    h.centre('Add', ['12']);
    lastLookup(h, 'roles').success({ roles: [{ value: 'Teacher', label: '<img src=x onerror=alert(1)>' }] });
    assert.doesNotMatch(h.$('#AddRole').html(), /<img/);
    assert.match(h.$('#AddRole').html(), /&lt;img/);
});

test('edit with no explicit current role stays blank until an assignable role is chosen', () => {
    const h = harness();
    h.click('#getEditData', 84);
    h.requests[0].success({ ...userResponse(84, 12), html: '<option value="" selected>Please select</option><option value="Teacher">Teacher</option>' });
    assert.equal(h.$('#editRole').val(), '');
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), true);
    h.click('#SubmitEditForm');
    assert.equal(h.requests.length, 1);
    h.$('#editRole').val('Teacher').trigger('change');
    assert.equal(h.$('#SubmitEditForm').prop('disabled'), false);
});

test('failed Edit lookup keeps Save blocked and succeeds on fresh reopen', () => {
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

test('create and update send a scalar role and explicit null department when unselected', () => {
    const h = harness();
    h.centre('Add', ['12']);
    resolveCreate(h);
    h.$('#AddPosition').val([]);
    h.click('#SubmitCreateForm');
    assert.equal(h.requests.at(-1).data.position_id, null);
    assert.equal(h.requests.at(-1).data.department_id, '12');
    assert.equal(h.requests.at(-1).data.role, 'Teacher');
    h.click('#getEditData', 84);
    h.requests.at(-1).success(userResponse(84, 12));
    h.$('#EditPosition').val([]);
    h.click('#SubmitEditForm');
    assert.equal(h.requests.at(-1).data.position, null);
    assert.equal(h.requests.at(-1).data.department, '12');
    assert.equal(h.requests.at(-1).data.role, 'BranchAdmin');
    assert.equal(h.requests.at(-1).url, '/users/save/84');
});
