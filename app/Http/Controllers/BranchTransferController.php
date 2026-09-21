<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchTransferRejected;
use App\Support\Branch\BranchTransferService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BranchTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:branch-transfer']);
    }

    public function index(Request $request)
    {
        abort_unless(BranchAccess::isCentral($request->user()), 403);
        $source_branches = Department::query()->orderBy('name')->get(['id', 'name', 'status']);
        $branches = $source_branches->where('status', 1)->values();
        $positions = \App\Models\Position::query()->where('status', 1)->get(['id', 'name', 'department_id']);
        $transfer_config = [
            'targets_url' => route('branch-transfers.targets'),
            'store_url' => route('branch-transfers.store'),
            'max_batch_size' => (int) config('branch.transfers.max_batch_size'),
            'positions' => $positions,
            'ui' => __('centre-management.transfers'),
        ];
        return view('branches.transfers', compact('source_branches', 'branches', 'transfer_config'));
    }

    public function targets(Request $request)
    {
        abort_unless(BranchAccess::isCentral($request->user()), 403);
        $request->validate(['paginated' => 'sometimes|boolean']);
        $is_paginated = $request->boolean('paginated');
        if ($is_paginated && is_string($request->input('q'))) {
            $search_query = trim($request->input('q'));
            $request->merge(['q' => $search_query === '' ? null : $search_query]);
        }
        $data = $request->validate([
            'type' => 'required|in:user,student',
            'q' => ($is_paginated ? 'nullable' : 'required').'|string|min:2|max:100',
            'from_branch_id' => $is_paginated ? 'nullable|integer|min:1' : 'exclude',
            'page' => $is_paginated ? 'sometimes|integer|min:1' : 'exclude',
        ]);
        $is_user = $data['type'] === 'user';
        $query = $is_user
            ? User::query()->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('name', ['SystemAdmin', 'Admin']))
            : Contact::query();
        $column = $is_user ? 'department_id' : 'centre';
        if (isset($data['from_branch_id'])) {
            BranchAccess::assertBranch($request->user(), $data['from_branch_id']);
            $query->where($column, $data['from_branch_id']);
        }
        if (isset($data['q'])) {
            $query->where(function ($names) use ($data, $is_user) {
                $names->where('name', 'like', '%'.$data['q'].'%');
                if (!$is_user) {
                    $names->orWhere('code', 'like', '%'.$data['q'].'%');
                }
            });
        }
        $query->orderBy('name')->orderBy('id');
        $page = $is_paginated ? $query->paginate((int) config('branch.transfers.page_size')) : null;
        $records = $page ? $page->getCollection() : $query->limit(30)->get();
        $branches = Department::whereIn('id', $records->pluck($column))->pluck('name', 'id');
        $results = $records->map(function ($record) use ($is_user, $column, $branches, $is_paginated) {
            $branch_name = $branches[$record->$column] ?? '';
            $result = [
                'id' => $record->id,
                'label' => $record->name.(!$is_user ? ' ('.$record->code.')' : '').' — '.$branch_name,
            ];
            return $is_paginated ? $result + [
                'name' => $record->name,
                'code' => $is_user ? null : $record->code,
                'email' => $is_user ? $record->email : null,
                'from_branch_id' => (int) $record->$column,
                'from_branch_name' => $branch_name,
            ] : $result;
        });
        $response = ['data' => $results];
        if ($page) {
            $response['meta'] = [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ];
        }
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless(BranchAccess::isCentral($actor), 403);
        if ($request->exists('record_ids')) {
            return $this->StoreBatch($request);
        }

        $data = $request->validate([
            'type' => 'required|in:student,user',
            'record_id' => 'required|integer', 'to_branch_id' => 'required|integer',
            'position_id' => 'nullable|integer', 'reason' => 'required|string|min:5|max:1000',
        ], [
            'type.required' => __('centre-management.transfers.validation.type_required'),
            'type.in' => __('centre-management.transfers.validation.type_invalid'),
            'record_id.required' => __('centre-management.transfers.validation.record_required'),
            'record_id.integer' => __('centre-management.transfers.validation.record_integer'),
            'to_branch_id.required' => __('centre-management.transfers.validation.destination_required'),
            'to_branch_id.integer' => __('centre-management.transfers.validation.destination_integer'),
            'position_id.integer' => __('centre-management.transfers.validation.position_integer'),
            'reason.required' => __('centre-management.transfers.validation.reason_required'),
            'reason.string' => __('centre-management.transfers.validation.reason_string'),
            'reason.min' => __('centre-management.transfers.validation.reason_min'),
            'reason.max' => __('centre-management.transfers.validation.reason_max'),
        ]);
        BranchAccess::assertBranch($actor, $data['to_branch_id'], true);
        $result = app(BranchTransferService::class)->Transfer($actor, $data, false)[0];
        return $request->expectsJson()
            ? response()->json(['success' => true, 'data' => $result])
            : back()->with('success', __('centre-management.transfers.messages.success'));
    }

    private function StoreBatch(Request $request)
    {
        if ($request->exists('record_id')) {
            throw ValidationException::withMessages([
                'record_ids' => __('centre-management.transfers.bulk.invalid_input'),
            ]);
        }

        $data = $request->validate([
            'type' => 'required|in:student,user',
            'record_ids' => 'required|array|min:1|max:'.config('branch.transfers.max_batch_size'),
            'record_ids.*' => 'required|integer|min:1|distinct',
            'expected_from_branch_ids' => 'required|array',
            'expected_from_branch_ids.*' => 'required|integer|min:1',
            'to_branch_id' => 'required|integer|min:1',
            'position_id' => 'nullable|integer|min:1|prohibited_unless:type,user',
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'required' => __('centre-management.transfers.bulk.invalid_input'),
            'array' => __('centre-management.transfers.bulk.invalid_input'),
            'integer' => __('centre-management.transfers.bulk.invalid_input'),
            'in' => __('centre-management.transfers.bulk.invalid_input'),
            'min' => __('centre-management.transfers.bulk.invalid_input'),
            'max' => __('centre-management.transfers.bulk.invalid_input'),
            'string' => __('centre-management.transfers.bulk.invalid_input'),
            'distinct' => __('centre-management.transfers.bulk.invalid_input'),
            'prohibited_unless' => __('centre-management.transfers.bulk.invalid_input'),
            'record_ids.max' => __('centre-management.transfers.bulk.limit'),
            'reason.min' => __('centre-management.transfers.validation.reason_min'),
            'reason.max' => __('centre-management.transfers.validation.reason_max'),
        ]);
        if (!array_is_list($data['record_ids'])) {
            throw ValidationException::withMessages([
                'record_ids' => __('centre-management.transfers.bulk.invalid_input'),
            ]);
        }
        $data['record_ids'] = array_map('intval', $data['record_ids']);
        $expected_keys = array_map('strval', $data['record_ids']);
        $actual_keys = array_map('strval', array_keys($data['expected_from_branch_ids']));
        sort($expected_keys, SORT_STRING);
        sort($actual_keys, SORT_STRING);
        if ($expected_keys !== $actual_keys) {
            throw ValidationException::withMessages([
                'expected_from_branch_ids' => __('centre-management.transfers.bulk.invalid_input'),
            ]);
        }
        $data['expected_from_branch_ids'] = array_map('intval', $data['expected_from_branch_ids']);

        try {
            $results = app(BranchTransferService::class)->Transfer($request->user(), $data, true);
        } catch (BranchTransferRejected $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'record_errors' => $exception->record_errors,
                ], $exception->http_status);
            }

            return back()->withInput()->withErrors(array_column($exception->record_errors, 'message'));
        }

        $message = __('centre-management.transfers.bulk.success', ['count' => count($results)]);
        return $request->expectsJson()
            ? response()->json([
                'success' => true,
                'message' => $message,
                'transferred_count' => count($results),
                'data' => $results,
            ])
            : back()->with('success', $message);
    }
}
