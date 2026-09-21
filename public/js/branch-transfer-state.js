export function CreateTransferSelection(max_count) {
    const selected_records = new Map();
    let filters = { type: 'student', from_branch_id: '', q: '' };

    function ApplyFilters(next_filters) {
        const normalized = {
            type: next_filters.type,
            from_branch_id: String(next_filters.from_branch_id ?? ''),
            q: String(next_filters.q ?? '').trim(),
        };
        const has_changed = JSON.stringify(normalized) !== JSON.stringify(filters);
        if (has_changed) {
            selected_records.clear();
        }
        filters = normalized;
        return has_changed;
    }

    function Toggle(record, is_checked) {
        const record_id = Number(record.id);
        if (!is_checked) {
            selected_records.delete(record_id);
            return true;
        }
        if (selected_records.has(record_id)) {
            return true;
        }
        if (selected_records.size >= max_count) {
            return false;
        }
        selected_records.set(record_id, Object.freeze({ ...record }));
        return true;
    }

    function SelectPage(records, is_checked) {
        const new_ids = new Set(
            records
                .filter(record => !selected_records.has(Number(record.id)))
                .map(record => Number(record.id)),
        );
        if (is_checked && selected_records.size + new_ids.size > max_count) {
            return false;
        }
        records.forEach(record => Toggle(record, is_checked));
        return true;
    }

    function PageState(records) {
        const selected_count = records.filter(record => selected_records.has(Number(record.id))).length;
        return {
            checked: records.length > 0 && selected_count === records.length,
            mixed: selected_count > 0 && selected_count < records.length,
        };
    }

    function Snapshot() {
        return [...selected_records.values()].map(record => ({ ...record }));
    }

    function BuildPayload(details) {
        const records = Snapshot();
        const payload = {
            type: filters.type,
            record_ids: records.map(record => Number(record.id)),
            expected_from_branch_ids: Object.fromEntries(
                records.map(record => [record.id, Number(record.from_branch_id)]),
            ),
            to_branch_id: Number(details.to_branch_id),
            reason: details.reason,
        };
        if (filters.type === 'user') {
            payload.position_id = details.position_id === '' || details.position_id == null
                ? null
                : Number(details.position_id);
        }
        return payload;
    }

    return {
        ApplyFilters,
        Toggle,
        SelectPage,
        PageState,
        Snapshot,
        BuildPayload,
        Count: () => selected_records.size,
        Has: record_id => selected_records.has(Number(record_id)),
        Remove: record_id => selected_records.delete(Number(record_id)),
        Clear: () => selected_records.clear(),
        Filters: () => ({ ...filters }),
    };
}

export function CreateTransferRequests(dependencies) {
    let search_revision = 0;
    let search_controller = null;
    let is_submitting = false;

    function Invalidate() {
        search_revision += 1;
        search_controller?.abort();
    }

    async function Load(filters, page_number) {
        Invalidate();
        search_controller = new AbortController();
        const revision = search_revision;
        try {
            const payload = await dependencies.FetchTargets(filters, page_number, search_controller.signal);
            if (revision !== search_revision) {
                return { kind: 'obsolete' };
            }
            return { kind: 'success', payload };
        } catch {
            return { kind: revision === search_revision ? 'error' : 'obsolete' };
        }
    }

    async function Submit(payload) {
        if (is_submitting) {
            return { kind: 'ignored' };
        }
        is_submitting = true;
        Invalidate();
        try {
            const response = await dependencies.SendTransfer(payload);
            const result = await response.json();
            if (
                response.ok
                && result?.success === true
                && result.transferred_count === payload.record_ids.length
                && Array.isArray(result.data)
                && result.data.length === payload.record_ids.length
            ) {
                return { kind: 'success', payload: result };
            }
            if (
                !response.ok
                && [401, 403, 409, 419, 422].includes(response.status)
                && result
                && typeof result === 'object'
            ) {
                return { kind: 'rejected', payload: result, status: response.status };
            }
            return { kind: 'uncertain' };
        } catch {
            return { kind: 'uncertain' };
        } finally {
            is_submitting = false;
        }
    }

    return {
        Load,
        Submit,
        Invalidate,
        IsSubmitting: () => is_submitting,
    };
}
