// Run from backend root: node tests/JavaScript/user-department-select2.fixture.mjs <output-directory>
// Optional USER_SCRIPT_PATH renders a saved before/after version of the real Blade script.
// This generates a standalone browser page; it does not launch a browser or write application data.
import { readFileSync, mkdirSync, writeFileSync, existsSync } from 'node:fs';
import { resolve, join } from 'node:path';
import { createHash } from 'node:crypto';

const root = process.env.BACKEND_ROOT
    ? resolve(process.env.BACKEND_ROOT)
    : existsSync(resolve('resources/views/users/script.blade.php')) ? resolve('.') : resolve('../eiMaths');
const scriptPath = process.env.USER_SCRIPT_PATH
    ? resolve(process.env.USER_SCRIPT_PATH) : join(root, 'resources/views/users/script.blade.php');
const output = resolve(process.argv[2] || 'tmp/user-department-select2');
const ui = {
    messages: { confirm_save: 'Save?', confirm_delete: 'Delete?', department_load_failed: 'Departments unavailable', user_load_failed: 'User unavailable', role_load_failed: 'Roles unavailable', no_assignable_roles: 'No assignable roles', assignable_roles_hint: 'Choose an allowed role for this centre', edit_roles_hint: 'Keep the current role or choose an allowed role' },
    users: { modal: { select: 'Please select' } }, data_table: {},
};
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
if (source.includes('{{') || source.includes('@json')) throw new Error('Unrendered Blade expression in fixture');
const hash = createHash('sha256').update(source).digest('hex').slice(0, 16);
const script = text => `<script>${text.replace(/<\/script/gi, '<\\/script')}</script>`;
const centre = id => `<label for="${id}">Centre</label><select id="${id}" class="select2_single" multiple style="width:100%"><option value="34">Branch 34</option><option value="56">Branch 56</option></select>`;
const department = id => `<label for="${id}">Department</label><select id="${id}" class="select2_single" multiple style="width:100%"></select>`;

const setup = String.raw`
window.fixtureRequests = [];
window.fixtureErrors = [];
window.confirm = () => true;
window.alert = message => fixtureErrors.push(String(message));
window.toastr = { error: message => fixtureErrors.push(String(message)), success() {} };
$.ajax = options => { fixtureRequests.push(options); return {}; };
$.ajaxSetup = () => {};
$.fn.modal = function(action) { this.attr('data-modal-state', action); return this; };
const unrelatedTable = { ajax: {reload(){}}, buttons: () => ({container: () => ({appendTo(){}})}), row: () => ({remove: () => ({draw(){}})}) };
$.fn.DataTable = () => unrelatedTable;
`;

const checks = String.raw`
$(function() {
    const output = document.getElementById('results');
    const summary = document.getElementById('summary');
    const tick = () => new Promise(resolve => setTimeout(resolve, 0));
    const assert = (condition, message) => { if (!condition) throw new Error(message); };
    const equals = (actual, expected, message) => assert(JSON.stringify(actual) === JSON.stringify(expected), message + ': ' + JSON.stringify(actual));
    const selected = selector => Array.from(document.querySelector(selector).selectedOptions, option => option.value);
    const options = extra => '<option value="">No department</option><option value="12">Admin</option><option value="84">Operations</option>' + (extra || '');
    let serial = 100;
    const response = (id, position = null, extra = '') => ({
        data: { id, department_id: 34, position_id: position, name: 'User ' + id, email: 'user' + id + '@example.test', disable: 0 },
        html: '<option value="BranchAdmin" selected>BranchAdmin</option>', select_list_position: options(extra),
    });
    function latest() { return fixtureRequests[fixtureRequests.length - 1]; }
    const lookup = kind => fixtureRequests.findLast(request => kind === 'roles' ? request.url === '/users/role-options' : request.url.startsWith('/departments/find/'));
    const roleResponse = (...names) => ({ roles: names.map(name => ({ value: name, label: name })) });
    async function openEdit(position = null, extra = '') {
        const id = ++serial;
        $('#getEditData').data('id', id).trigger('click');
        const request = latest();
        assert(request.method === 'GET', 'Edit must fetch its current data');
        request.success(response(id, position, extra));
        await tick();
        return id;
    }
    function noSelection(selector) {
        equals(selected(selector), [], 'Native DOM must have no selected options');
        equals($(selector).select2('data').map(item => item.id), [], 'Select2 must have no hidden selected placeholder');
    }
    async function openDropdown(selector) {
        $('.select2-hidden-accessible').each(function() { $(this).select2('close'); });
        const widget = $(selector).data('select2');
        widget.$selection.trigger('click');
        await tick();
        assert(widget.isOpen(), 'Widget must open through selection click');
        const text = widget.$results.text();
        assert(!text.includes('You can only select'), 'Empty widget must not report maximum selection: ' + text);
        return widget;
    }
    async function choose(selector, label, expectedId) {
        const widget = await openDropdown(selector);
        const item = Array.from(widget.$results[0].querySelectorAll('.select2-results__option[aria-selected]'))
            .find(element => element.textContent.trim() === label);
        assert(item, 'Actual dropdown must contain ' + label);
        $(item).trigger('mouseup');
        await tick();
        equals(selected(selector), [String(expectedId)], 'Selecting rendered result must update the real select');
        equals($(selector).select2('data').map(item => item.id), [String(expectedId)], 'Select2 selection must match full identifier');
    }
    async function clearChip(selector) {
        const widget = $(selector).data('select2');
        const remove = widget.$selection.find('.select2-selection__choice__remove');
        assert(remove.length === 1, 'Exactly one selected chip must have a remove control');
        remove.trigger('click');
        await tick();
        $(selector).select2('close');
        noSelection(selector);
    }
    const cases = [
        ['Edit: null Department, blank option, real selection and complete update IDs', async () => {
            const id = await openEdit(null);
            noSelection('#EditPosition');
            await choose('#EditPosition', 'Admin', 12);
            $('#SubmitEditForm').trigger('click');
            equals(latest().data.position, '12', 'PUT must send complete Department ID');
            equals(latest().data.department, '34', 'PUT must send complete Centre ID');
            equals(latest().url, '/users/save/' + id, 'PUT must target the loaded user');
        }],
        ['Edit: empty-string Department leaves no hidden placeholder selection', async () => {
            await openEdit('');
            noSelection('#EditPosition');
            await choose('#EditPosition', 'Operations', 84);
        }],
        ['Edit: existing Department survives loading, clear chip then select another', async () => {
            await openEdit(84);
            equals(selected('#EditPosition'), ['84'], 'Existing assignment must be retained');
            await clearChip('#EditPosition');
            await choose('#EditPosition', 'Admin', 12);
        }],
        ['Create: fresh Centre response has no phantom selection; actual selection submits full IDs', async () => {
            $('#AddDepartment').val(['34']).trigger('change');
            lookup('departments').success({ html: options() });
            assert($('#SubmitCreateForm').prop('disabled'), 'Save waits for the independent role request');
            lookup('roles').success(roleResponse('Teacher'));
            equals($('#AddRole').val(), '', 'Loading roles must not silently select the first option');
            assert($('#SubmitCreateForm').prop('disabled'), 'Save waits for an explicit role selection');
            $('#AddRole').val('Teacher').trigger('change');
            await tick();
            noSelection('#AddPosition');
            await choose('#AddPosition', 'Operations', 84);
            $('#SubmitCreateForm').trigger('click');
            equals(latest().data.position_id, '84', 'POST must send complete Department ID');
            equals(latest().data.department_id, '34', 'POST must send complete Centre ID');
            equals(latest().data.role, 'Teacher', 'POST must send the explicitly selected scalar role');
        }],
        ['Edit: reopen reloads new Department and clears previous-user selection', async () => {
            await openEdit(84);
            await openEdit(null, '<option value="125">New Department</option>');
            noSelection('#EditPosition');
            await choose('#EditPosition', 'New Department', 125);
        }],
        ['Edit: failed request blocks Save, reopening retries and permits actual selection', async () => {
            $('#getEditData').data('id', ++serial).trigger('click');
            latest().error({});
            assert($('#SubmitEditForm').prop('disabled'), 'Save remains blocked after loading failure');
            await openEdit(null);
            noSelection('#EditPosition');
            await choose('#EditPosition', 'Admin', 12);
            assert(!$('#SubmitEditForm').prop('disabled'), 'Retry success enables Save');
        }],
        ['Create: failed lookup then reopen retries without selecting blank option', async () => {
            $('#AddDepartment').val(['56']).trigger('change');
            lookup('departments').error({});
            assert($('#SubmitCreateForm').prop('disabled'), 'Failed Centre lookup blocks Save');
            $('#CreateButton').trigger('click');
            assert(lookup('departments').url.endsWith('/56'), 'Reopening must retry the selected Centre');
            equals(lookup('roles').data.department_id, '56', 'Reopening must refetch assignable roles for the selected Centre');
            lookup('departments').success({ html: options() });
            lookup('roles').success(roleResponse('Teacher'));
            $('#AddRole').val('Teacher').trigger('change');
            await tick();
            noSelection('#AddPosition');
            await choose('#AddPosition', 'Admin', 12);
        }],
        ['Edit: clearing existing assignment submits null instead of a blank selected item', async () => {
            await openEdit(84);
            await clearChip('#EditPosition');
            $('#SubmitEditForm').trigger('click');
            equals(latest().data.position, null, 'Cleared Department submits explicit null');
        }],
        ['Create: empty assignable roles explain the restriction and keep Save blocked', async () => {
            $('#AddDepartment').val(['34']).trigger('change');
            lookup('departments').success({ html: options() });
            lookup('roles').success(roleResponse());
            assert($('#AddRole').prop('disabled'), 'An empty role dropdown stays disabled');
            assert($('#SubmitCreateForm').prop('disabled'), 'No role means Save is blocked');
            equals($('#AddRoleHelp').text(), 'No assignable roles', 'Role restriction must be explained beside the control');
            const count = fixtureRequests.length;
            $('#SubmitCreateForm').trigger('click');
            equals(fixtureRequests.length, count, 'Programmatic Save cannot bypass the empty-role guard');
        }],
        ['Create: late old-centre roles cannot overwrite current allowed options', async () => {
            $('#AddDepartment').val(['34']).trigger('change');
            const oldDepartment = lookup('departments'), oldRole = lookup('roles');
            $('#AddDepartment').val(['56']).trigger('change');
            lookup('departments').success({ html: options() });
            lookup('roles').success(roleResponse('Teacher'));
            $('#AddRole').val('Teacher').trigger('change');
            oldDepartment.success({ html: '<option value="999">Wrong centre</option>' });
            oldRole.success(roleResponse('SystemAdmin'));
            equals(Array.from(document.querySelector('#AddRole').options, option => option.value), ['', 'Teacher'], 'Only current-centre roles remain');
            equals($('#AddRole').val(), 'Teacher', 'Current explicit role selection is retained');
            assert(!$('#SubmitCreateForm').prop('disabled'), 'Current complete selections stay usable');
        }],
    ];
    window.fixtureResults = [];
    (async () => {
        await tick();
        for (const [name, run] of cases) {
            const row = document.createElement('li');
            try { await run(); row.className = 'pass'; row.textContent = 'PASS — ' + name; fixtureResults.push({ name, pass: true }); }
            catch (error) { row.className = 'fail'; row.textContent = 'FAIL — ' + name + ': ' + error.message; fixtureResults.push({ name, pass: false, error: error.message }); }
            output.append(row);
        }
        const failed = fixtureResults.filter(result => !result.pass).length;
        summary.textContent = (failed ? 'FAIL' : 'PASS') + ': ' + (cases.length - failed) + '/' + cases.length + ' real-widget cases';
        summary.className = failed ? 'fail' : 'pass';
        document.documentElement.dataset.fixtureStatus = failed ? 'failed' : 'passed';
        document.documentElement.dataset.fixtureDone = 'true';
        $('.select2-hidden-accessible').each(function() { $(this).select2('close'); });
    })().catch(error => { summary.textContent = 'FIXTURE ERROR: ' + error.message; document.documentElement.dataset.fixtureDone = 'true'; });
});
`;

const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="csrf-token" content="fixture-token"><title>User Department Select2 regression</title>
<style>${readFileSync(join(root, 'public/plugins/select2/css/select2.min.css'), 'utf8')} body{font:15px system-ui,sans-serif;max-width:1100px;margin:24px auto;padding:0 18px}h1{font-size:24px}.pass{color:#176a34}.fail{color:#b21b24}li{margin:9px 0}section{display:grid;grid-template-columns:1fr 1fr;gap:24px}form{padding:18px;border:1px solid #ccd4df;border-radius:8px}label{display:block;margin:12px 0 4px}input{max-width:160px;margin:8px}button{margin:10px 5px 0 0;padding:7px}#results{padding:0 0 0 22px}</style></head><body>
<h1>Real jQuery + Select2 Department regression</h1><p>Source SHA-256: <code>${hash}</code>. AJAX requests are captured locally; no server mutation occurs.</p>
<p id="summary">Running actual DOM/widget checks…</p><ol id="results"></ol>
<button id="getEditData" data-id="101">Edit fixture user</button><button id="CreateButton">Create fixture user</button>
<section><div id="EditModal"><form><h2>Edit User</h2><input id="editName"><input id="editEmail">${centre('EditDepartment')}${department('EditPosition')}<input id="EditPassword" type="password"><input id="EditPasswordC" type="password"><select id="editRole"></select><small id="EditRoleHelp"></small><input type="checkbox" id="ecustomCheckbox1"><button id="SubmitEditForm">Save Edit</button></form></div>
<div id="CreateModal"><form><h2>Create User</h2><input id="AddName"><input id="AddEmail">${centre('AddDepartment')}${department('AddPosition')}<input id="AddPassword" type="password"><input id="AddPasswordc" type="password"><select id="AddRole"><option value="">Please select</option></select><small id="AddRoleHelp"></small><button id="SubmitCreateForm">Save Create</button></form></div></section>
<div class="alert-danger"></div><div class="alert-success"></div><table id="Listview"></table>
${script(readFileSync(join(root, 'public/plugins/jquery/jquery.js'), 'utf8'))}
${script(readFileSync(join(root, 'public/plugins/select2/js/select2.full.js'), 'utf8'))}
${script(setup)}${script(source)}${script(checks)}</body></html>`;
mkdirSync(output, { recursive: true });
const destination = join(output, 'user-department-select2.html');
writeFileSync(destination, html, 'utf8');
console.log(JSON.stringify({ path: destination, source: scriptPath, sourceHash: hash, cases: 10 }));
