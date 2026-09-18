<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Support\Branch\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:centre-manage|centre-list|centre-create|centre-edit|centre-delete', ['only' => ['index', 'show', 'edit']]);
        $this->middleware('permission:centre-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:centre-edit', ['only' => ['update']]);
        $this->middleware('permission:centre-delete', ['only' => ['destroy', 'destroy_all']]);
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('departments.index');
        }
        return datatables()->of(Department::query()->orderByDesc('id'))
            ->editColumn('checkbox', fn ($row) => $row->id == Department::HQ_ID ? '' : '<input type="checkbox" name="table_records[]" value="'.(int) $row->id.'">')
            ->editColumn('status', fn ($row) => $row->status == 1
                ? __('centre-management.messages.status_active')
                : __('centre-management.messages.status_archived'))
            ->addColumn('action', function ($row) use ($request) {
                $html = $request->user()->can('centre-edit')
                    ? '<button class="btn btn-sm btn-warning" id="getEditData" data-id="'.(int) $row->id.'">'.e(__('centre-management.centres.actions.edit')).'</button> '
                    : '';
                if ($request->user()->can('centre-delete') && $row->id != Department::HQ_ID && $row->status == 1) {
                    $html .= '<button class="btn btn-sm btn-danger btn-delete" data-rowid="'.(int) $row->id.'">'.e(__('centre-management.centres.actions.archive')).'</button>';
                }
                return $html;
            })->rawColumns(['checkbox', 'action'])->toJson();
    }

    public function find($type, $department)
    {
        BranchAccess::assertBranch(request()->user(), $department);
        $html = '<option value="">'.e(__('centre-management.messages.no_department')).'</option>';
        foreach (Position::where('department_id', $department)->where('status', 1)->orderBy('name')->get() as $position) {
            $html .= '<option value="'.(int) $position->id.'">'.e($position->name).'</option>';
        }
        return response()->json(['html' => $html]);
    }

    public function create()
    {
        return view('departments.create');
    }

    private function data(Request $request, ?Department $branch = null): array
    {
        abort_unless(BranchAccess::isCentral($request->user()), 403);
        if ($request->boolean('modules_submitted') && !$request->has('enabled_modules')) {
            $request->merge(['enabled_modules' => []]);
        }
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($branch?->id)],
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('departments', 'code')->ignore($branch?->id)],
            'status' => 'required|boolean',
            'enabled_modules' => 'sometimes|array',
            'enabled_modules.*' => ['string', Rule::in(array_keys(config('branch.modules')))],
        ], [
            'name.required' => __('centre-management.validation.name_required'),
            'name.unique' => __('centre-management.validation.name_unique'),
            'code.required' => __('centre-management.validation.code_required'),
            'code.unique' => __('centre-management.validation.code_unique'),
            'code.max' => __('centre-management.validation.code_max'),
            'code.regex' => __('centre-management.validation.code_regex'),
            'status.required' => __('centre-management.validation.status_required'),
            'status.boolean' => __('centre-management.validation.status_boolean'),
            'enabled_modules.array' => __('centre-management.validation.modules_array'),
            'enabled_modules.*.in' => __('centre-management.validation.module_invalid'),
        ]);
        if ($request->has('enabled_modules')) {
            abort_unless($request->user()->can('branch-modules-manage'), 403, __('centre-management.messages.module_permission_required'));
            $data['enabled_modules'] = array_values(array_unique($data['enabled_modules']));
        } elseif (!$branch) {
            $data['enabled_modules'] = config('branch.default_modules');
        }
        if ($branch?->id === Department::HQ_ID) {
            abort_unless((int) $data['status'] === 1, 422, __('centre-management.messages.hq_cannot_archive'));
        }
        if ($branch && $branch->code !== $data['code']) {
            foreach (['contacts', 'orders', 'receipts', 'invoices', 'products'] as $table) {
                abort_if(DB::table($table)->where('centre', $branch->id)->exists(), 422, __('centre-management.messages.branch_code_in_use'));
            }
        }
        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        $branch = DB::transaction(fn () => Department::create($data + ['branch_type' => 'branch']));
        return response()->json(['success' => __('centre-management.messages.branch_created'), 'data' => $branch]);
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $branch = Department::findOrFail($id);
        $data = $branch->toArray();
        $data['enabled_modules'] = $branch->enabled_modules ?? array_keys(config('branch.modules'));
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $branch = Department::findOrFail($id);
        $data = $this->data($request, $branch);
        DB::transaction(fn () => $branch->update($data));
        return response()->json(['success' => __('centre-management.messages.branch_updated')]);
    }

    private function archive(array $ids): void
    {
        abort_unless(BranchAccess::isCentral(request()->user()), 403);
        $ids = array_unique(array_map('intval', $ids));
        abort_unless($ids && count($ids) <= 500, 422);
        abort_if(in_array(Department::HQ_ID, $ids, true), 422, __('centre-management.messages.hq_cannot_archive'));
        DB::transaction(function () use ($ids) {
            $branches = Department::whereKey($ids)->lockForUpdate()->get();
            abort_unless($branches->count() === count($ids), 404);
            foreach ($branches as $branch) {
                $branch->update(['status' => 0]);
            }
        });
    }

    public function destroy(Request $request)
    {
        $this->archive([(int) $request->id]);
        return response()->json(['success' => true, 'message' => __('centre-management.messages.branch_archived')]);
    }

    public function destroy_all(Request $request)
    {
        $request->validate(['table_records' => 'required|array', 'table_records.*' => 'integer']);
        $this->archive($request->table_records);
        return redirect('/departments')->with('success', __('centre-management.messages.branches_archived'));
    }
}
