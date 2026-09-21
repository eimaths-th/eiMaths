import { CreateTransferSelection, CreateTransferRequests } from './branch-transfer-state.js';

export function FormatTransferMessage(message, replacements = {}) {
    return Object.entries(replacements)
        .sort(([left_key], [right_key]) => right_key.length - left_key.length)
        .reduce((text, [key, replacement]) => text.replaceAll(':' + key, String(replacement)), message);
}

export function InitializeBranchTransferPage() {
    const root = document.getElementById('branch-transfer-page');
    if (!root) { return; }
    const config = JSON.parse(document.getElementById('transfer-config').textContent);
    const ui = config.ui;
    const selection = CreateTransferSelection(config.max_batch_size);
    const Find = id => root.querySelector('#' + id);
    const status = Find('transfer-status');
    const errors = Find('transfer-errors');
    const record_type = Find('transfer-type');
    const destination = Find('branch-id');
    const position = Find('transfer-position');
    const details_form = Find('transfer-details');
    let rows = [];
    let meta = { current_page: 1, last_page: 1, total: 0 };
    let is_loading = false;
    let is_reviewing = false;
    let is_uncertain = false;
    let review_details = null;
    const stale_ids = new Set();

    const Format = FormatTransferMessage;

    function TextElement(tag, text, class_name = '') {
        const element = document.createElement(tag);
        element.textContent = text ?? '';
        element.className = class_name;
        return element;
    }

    function ShowErrors(messages) {
        errors.replaceChildren(...messages.map(message => TextElement('p', message, 'mb-1')));
        errors.hidden = messages.length === 0;
        if (messages.length) { errors.focus(); }
    }

    const requests = CreateTransferRequests({
        FetchTargets: async (filters, page_number, signal) => {
            const url = new URL(config.targets_url, window.location.href);
            url.searchParams.set('paginated', '1');
            url.searchParams.set('type', filters.type);
            url.searchParams.set('page', String(page_number));
            if (filters.from_branch_id !== '') { url.searchParams.set('from_branch_id', filters.from_branch_id); }
            if (filters.q !== '') { url.searchParams.set('q', filters.q); }
            const response = await fetch(url, { signal, headers: { Accept: 'application/json' } });
            if (!response.ok) { throw new Error('Candidate request failed'); }
            const payload = await response.json();
            if (!Array.isArray(payload.data) || !payload.meta) { throw new Error('Invalid candidate response'); }
            return payload;
        },
        SendTransfer: payload => fetch(config.store_url, {
            method: 'POST',
            headers: {
                Accept: 'application/json', 'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        }),
    });

    function Render() {
        const focused_record_id = document.activeElement?.getAttribute('data-record-id');
        const is_busy = requests.IsSubmitting();
        const is_locked = is_busy || is_reviewing;
        Find('transfer-filter-fields').disabled = is_locked;
        Find('transfer-detail-fields').disabled = is_busy;
        details_form.hidden = is_reviewing;
        Find('transfer-review').hidden = !is_reviewing;
        Find('transfer-selected-count').textContent = Format(ui.bulk.selected, { count: selection.Count() });
        Find('transfer-clear').disabled = is_locked || selection.Count() === 0;
        Find('transfer-review-open').disabled = is_busy || selection.Count() === 0 || is_uncertain;
        Find('transfer-previous').disabled = is_locked || is_loading || meta.current_page <= 1;
        Find('transfer-next').disabled = is_locked || is_loading || meta.current_page >= meta.last_page;
        Find('transfer-page-info').textContent = Format(ui.bulk.page, { page: meta.current_page, pages: meta.last_page, total: meta.total });
        const page_state = selection.PageState(rows);
        const page_checkbox = Find('transfer-select-page');
        page_checkbox.checked = page_state.checked;
        page_checkbox.indeterminate = page_state.mixed;
        page_checkbox.disabled = is_locked || is_loading || rows.length === 0;
        const body = Find('transfer-rows');
        body.replaceChildren();
        for (const record of rows) {
            const table_row = document.createElement('tr');
            const checkbox_cell = document.createElement('td');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.setAttribute('data-record-id', String(record.id));
            checkbox.checked = selection.Has(record.id);
            checkbox.disabled = is_locked || is_loading || (!checkbox.checked && selection.Count() >= config.max_batch_size);
            checkbox.setAttribute('aria-label', Format(ui.bulk.select_person, { name: record.name }));
            checkbox.addEventListener('change', function HandleRecordSelection() {
                if (!selection.Toggle(record, checkbox.checked)) { ShowErrors([Format(ui.bulk.limit, { max: config.max_batch_size })]); }
                Render();
            });
            checkbox_cell.append(checkbox);
            table_row.append(checkbox_cell, TextElement('td', record.name), TextElement('td', record.code ?? record.email ?? ''), TextElement('td', record.from_branch_name));
            body.append(table_row);
        }
        if (!rows.length && !is_loading) {
            const table_row = document.createElement('tr');
            const cell = TextElement('td', ui.status.no_results);
            cell.colSpan = 4;
            table_row.append(cell);
            body.append(table_row);
        }
        Find('transfer-confirm').disabled = is_busy || is_uncertain || selection.Count() === 0
            || selection.Snapshot().some(record => stale_ids.has(Number(record.id)));
        Find('transfer-back').disabled = is_busy;
        Find('transfer-reload').disabled = is_busy;
        if (is_reviewing) { RenderReview(); }
        if (focused_record_id !== null && focused_record_id !== undefined) {
            body.querySelector('input[data-record-id="' + Number(focused_record_id) + '"]')?.focus();
        }
    }

    function RenderReview() {
        const summary = Find('transfer-review-summary');
        summary.replaceChildren(
            TextElement('p', Format(ui.bulk.selected, { count: selection.Count() })),
            TextElement('p', ui.fields.destination_branch + ': ' + review_details.destination_name),
            TextElement('p', ui.fields.reason + ': ' + review_details.reason),
        );
        if (selection.Filters().type === 'user') {
            summary.append(TextElement('p', ui.fields.destination_department + ': ' + review_details.position_name));
        }
        const list = Find('transfer-review-records');
        list.replaceChildren();
        for (const record of selection.Snapshot()) {
            const list_item = TextElement('li', record.name + ' (' + (record.code ?? record.email ?? '') + ') - ' + record.from_branch_name + ' ', 'list-group-item');
            const remove_button = TextElement('button', ui.bulk.remove, 'btn btn-outline-secondary btn-sm ml-2');
            remove_button.type = 'button';
            remove_button.disabled = requests.IsSubmitting();
            remove_button.addEventListener('click', function HandleRemove() {
                selection.Remove(record.id);
                stale_ids.delete(Number(record.id));
                Render();
                Find('transfer-review').focus();
            });
            list_item.append(remove_button);
            list.append(list_item);
        }
    }

    async function Load(page_number = 1) {
        is_loading = true;
        rows = [];
        status.textContent = ui.status.searching;
        Render();
        const result = await requests.Load(selection.Filters(), page_number);
        if (result.kind === 'obsolete') { return false; }
        is_loading = false;
        if (result.kind === 'error') { status.textContent = ui.status.search_failed; Render(); return false; }
        rows = result.payload.data;
        meta = result.payload.meta;
        if (meta.current_page > meta.last_page) { return Load(meta.last_page); }
        status.textContent = rows.length ? '' : ui.status.no_results;
        Render();
        return true;
    }

    function SetPositions() {
        const is_user = record_type.value === 'user';
        Find('transfer-position-group').hidden = !is_user;
        position.disabled = !is_user;
        position.replaceChildren(new Option(ui.options.no_department, ''));
        config.positions.filter(department => String(department.department_id) === destination.value)
            .forEach(department => position.add(new Option(department.name, department.id)));
        Find('transfer-search-label').textContent = is_user ? ui.bulk.user_search : ui.fields.search;
    }

    async function ApplyFilters(event) {
        event?.preventDefault();
        if (requests.IsSubmitting() || is_reviewing) { return; }
        const query = Find('transfer-search').value.trim();
        const query_length = Array.from(query).length;
        if (query !== '' && (query_length < 2 || query_length > 100)) { ShowErrors([ui.bulk.search_invalid]); return; }
        const had_selection = selection.Count() > 0;
        const changed = selection.ApplyFilters({ type: record_type.value, from_branch_id: Find('transfer-source').value, q: query });
        ShowErrors([]);
        if (changed) { stale_ids.clear(); }
        const did_load = await Load(1);
        if (changed && had_selection) { status.textContent = ui.bulk.cleared + (did_load ? '' : ' ' + ui.status.search_failed); }
    }

    Find('transfer-filters').addEventListener('submit', ApplyFilters);
    record_type.addEventListener('change', async function HandleTypeChange() {
        Find('transfer-search').value = '';
        SetPositions();
        await ApplyFilters();
    });
    destination.addEventListener('change', SetPositions);
    Find('transfer-previous').addEventListener('click', () => Load(meta.current_page - 1));
    Find('transfer-next').addEventListener('click', () => Load(meta.current_page + 1));
    Find('transfer-clear').addEventListener('click', function HandleClear() { selection.Clear(); stale_ids.clear(); Render(); });
    Find('transfer-select-page').addEventListener('change', function HandlePageSelection(event) {
        if (!selection.SelectPage(rows, event.target.checked)) { ShowErrors([Format(ui.bulk.limit, { max: config.max_batch_size })]); }
        Render();
    });
    details_form.addEventListener('submit', function HandleReview(event) {
        event.preventDefault();
        if (!details_form.reportValidity() || requests.IsSubmitting() || is_uncertain) { return; }
        if (!selection.Count()) { ShowErrors([ui.bulk.selection_empty]); return; }
        review_details = {
            to_branch_id: destination.value, position_id: position.value,
            destination_name: destination.selectedOptions[0]?.textContent ?? '',
            position_name: position.selectedOptions[0]?.textContent ?? ui.options.no_department,
            reason: Find('transfer-reason').value,
        };
        is_reviewing = true;
        ShowErrors([]);
        Render();
        Find('transfer-review').focus();
    });
    Find('transfer-back').addEventListener('click', function HandleBack() {
        is_reviewing = false;
        Render();
        if (!Find('transfer-review-open').disabled) { Find('transfer-review-open').focus(); }
        else { destination.focus(); }
    });
    Find('transfer-reload').addEventListener('click', () => window.location.reload());
    Find('transfer-confirm').addEventListener('click', async function HandleConfirm() {
        if (requests.IsSubmitting() || is_uncertain || !selection.Count()
            || selection.Snapshot().some(record => stale_ids.has(Number(record.id)))) { return; }
        const pending = requests.Submit(selection.BuildPayload(review_details));
        is_loading = false;
        status.textContent = ui.bulk.submitting;
        ShowErrors([]);
        Render();
        const result = await pending;
        if (result.kind === 'ignored') { return; }
        if (result.kind === 'success') {
            selection.Clear();
            stale_ids.clear();
            is_reviewing = false;
            const did_refresh = await Load(meta.current_page);
            status.textContent = result.payload.message + (did_refresh ? '' : ' ' + ui.status.search_failed);
            destination.focus();
        } else if (result.kind === 'rejected') {
            const record_map = new Map(selection.Snapshot().map(record => [Number(record.id), record]));
            const messages = [[401, 403, 419].includes(result.status) ? ui.bulk.forbidden : ui.bulk.failed];
            for (const problem of result.payload.record_errors ?? []) {
                const record = record_map.get(Number(problem.record_id));
                messages.push((record ? record.name + ': ' : '') + problem.message);
                if (problem.code === 'SOURCE_CHANGED') { stale_ids.add(Number(problem.record_id)); }
            }
            for (const field_messages of Object.values(result.payload.errors ?? {})) {
                messages.push(...(Array.isArray(field_messages) ? field_messages : [field_messages]).filter(message => typeof message === 'string'));
            }
            status.textContent = '';
            ShowErrors(messages);
            Find('transfer-reload').hidden = false;
        } else {
            is_uncertain = true;
            status.textContent = '';
            ShowErrors([ui.bulk.uncertain]);
            Find('transfer-reload').hidden = false;
        }
        Render();
    });
    SetPositions();
    void ApplyFilters();
}
