import test from 'node:test';
import assert from 'node:assert/strict';
import { CreateTransferSelection, CreateTransferRequests } from '../../public/js/branch-transfer-state.js';
import { FormatTransferMessage } from '../../public/js/branch-transfer-page.js';

const FIRST = { id: 101, name: 'First', from_branch_id: 2 };
const SECOND = { id: 102, name: 'Second', from_branch_id: 2 };
const THIRD = { id: 103, name: 'Third', from_branch_id: 3 };

test('FormatsOverlappingPagePlaceholdersWithoutCorruptingThePagesValue', () => {
    assert.equal(
        FormatTransferMessage('หน้า :page จาก :pages (ทั้งหมด :total รายการ)', { page: 3, pages: 10, total: 226 }),
        'หน้า 3 จาก 10 (ทั้งหมด 226 รายการ)',
    );
});

test('RetainsSelectionAcrossPagesAndClearsOnlyWhenAppliedFiltersChange', () => {
    const selection = CreateTransferSelection(100);
    selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: '' });
    selection.Toggle(FIRST, true);
    selection.Toggle(SECOND, true);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [101, 102]);
    assert.equal(selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: ' ' }), false);
    assert.equal(selection.Count(), 2);
    assert.equal(selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: 'New' }), true);
    assert.equal(selection.Count(), 0);
    selection.Toggle(FIRST, true);
    selection.ApplyFilters({ type: 'user', from_branch_id: '2', q: 'New' });
    assert.equal(selection.Count(), 0);
});

test('SelectPageHonorsTheLimitWithoutPartialSelection', () => {
    const selection = CreateTransferSelection(2);
    selection.Toggle(FIRST, true);
    assert.equal(selection.SelectPage([SECOND, THIRD], true), false);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [101]);
    assert.deepEqual(selection.PageState([FIRST, SECOND]), { checked: false, mixed: true });
    assert.equal(selection.SelectPage([FIRST, SECOND], true), true);
    assert.deepEqual(selection.PageState([FIRST, SECOND]), { checked: true, mixed: false });
    assert.equal(selection.Toggle(THIRD, true), false);
    selection.SelectPage([FIRST], false);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [102]);
});

test('PreservesOriginalSourcesAndDoesNotExposeMutableSelection', () => {
    const selection = CreateTransferSelection(100);
    selection.Toggle(FIRST, true);
    selection.Toggle({ ...FIRST, from_branch_id: 9 }, true);
    selection.Snapshot()[0].from_branch_id = 7;
    assert.deepEqual(selection.BuildPayload({ to_branch_id: '3', reason: 'Move now' }), {
        type: 'student',
        record_ids: [101],
        expected_from_branch_ids: { 101: 2 },
        to_branch_id: 3,
        reason: 'Move now',
    });
});

test('IgnoresAnOlderSearchAfterANewerSearchCompletes', async () => {
    const resolvers = [];
    const requests = CreateTransferRequests({
        FetchTargets: () => new Promise(resolve => resolvers.push(resolve)),
        SendTransfer: async () => { throw new Error('Unexpected submit'); },
    });
    const first_request = requests.Load({ type: 'student', q: 'First' }, 1);
    const second_request = requests.Load({ type: 'student', q: 'Second' }, 1);
    resolvers[1]({ data: [SECOND], meta: { current_page: 1 } });
    assert.equal((await second_request).payload.data[0].id, 102);
    resolvers[0]({ data: [FIRST], meta: { current_page: 1 } });
    assert.equal((await first_request).kind, 'obsolete');
});

test('PreventsDuplicateSubmitAndReturnsBusinessErrorsWithoutRetrying', async () => {
    let resolve_response;
    let send_count = 0;
    const requests = CreateTransferRequests({
        FetchTargets: async () => ({ data: [] }),
        SendTransfer: () => {
            send_count += 1;
            return new Promise(resolve => { resolve_response = resolve; });
        },
    });
    const payload = { record_ids: [101] };
    const pending = requests.Submit(payload);
    assert.equal((await requests.Submit(payload)).kind, 'ignored');
    resolve_response({
        ok: false,
        status: 409,
        json: async () => ({
            success: false,
            record_errors: [{ record_id: 101, code: 'SOURCE_CHANGED', message: 'Reload' }],
        }),
    });
    assert.equal((await pending).kind, 'rejected');
    assert.equal(send_count, 1);
    assert.equal(requests.IsSubmitting(), false);
});

test('SeparatesConfirmedSuccessFromUnknownNetworkOutcomes', async () => {
    let send_count = 0;
    const requests = CreateTransferRequests({
        FetchTargets: async () => ({ data: [] }),
        SendTransfer: async () => {
            send_count += 1;
            if (send_count === 1) { throw new Error('Connection interrupted'); }
            return {
                ok: true,
                status: 200,
                json: async () => ({
                    success: true,
                    transferred_count: 1,
                    data: [{ id: 101 }],
                    message: 'Done',
                }),
            };
        },
    });
    assert.equal((await requests.Submit({ record_ids: [101] })).kind, 'uncertain');
    assert.equal(send_count, 1);
    assert.equal((await requests.Submit({ record_ids: [101] })).kind, 'success');
});
