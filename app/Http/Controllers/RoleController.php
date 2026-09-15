<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use App\Support\Branch\PermissionPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:role-list', ['only' => ['index', 'show', 'template']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy', 'destroy_all']]);
    }

    private function canEdit(Role $role): bool
    {
        $actor = request()->user();
        if ($role->name === 'SystemAdmin') { return false; }
        return BranchAccess::isOwner($actor)
            || ($role->branch_id !== null && (BranchAccess::canCrossBranches($actor) || (int) $role->branch_id === (int) $actor->department_id));
    }

    private function permissions()
    {
        $actor = request()->user();
        return Permission::where('guard_name', 'web')->get()->filter(fn ($p) => BranchAccess::isOwner($actor) || (BranchAccess::permissionAllowed($actor, $p->name) && $actor->checkPermissionTo($p->name)));
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $permission = $this->permissions();
            $permissionGroups = PermissionPresenter::groups($permission);
            $branches = Department::where('status', 1)->get();
            return view('roles.index', compact('permission', 'permissionGroups', 'branches'));
        }
        return datatables()->of(BranchAccess::visibleRoles($request->user())->with('permissions')->orderByDesc('id'))
            ->editColumn('name', fn ($role) => ($role->display_name ?: $role->name).($role->branch_id ? ' [สาขา '.$role->branch_id.']' : ' [มาตรฐานกลาง]'))
            ->editColumn('checkbox', fn ($role) => $this->canEdit($role) ? '<input type="checkbox" name="table_records[]" value="'.(int) $role->id.'">' : '')
            ->editColumn('role', fn ($role) => $role->permissions->pluck('name')->implode(', '))
            ->addColumn('action', function ($role) use ($request) {
                if (!$this->canEdit($role)) { return 'แม่แบบกลาง'; }
                $html = $request->user()->can('role-edit') ? '<button class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="'.(int) $role->id.'">Edit</button> ' : '';
                if ($request->user()->can('role-delete') && !in_array($role->name, config('branch.protected_roles'), true)) {
                    $html .= '<button class="btn btn-sm btn-danger btn-delete" data-rowid="'.(int) $role->id.'">Delete</button>';
                }
                return $html;
            })->rawColumns(['checkbox', 'action'])->toJson();
    }

    public function template($id)
    {
        $role = BranchAccess::visibleRoles(request()->user())->findOrFail($id);
        $allowed = $this->permissions()->pluck('id')->all();
        return response()->json(['permission' => $role->permissions->pluck('id')->filter(fn ($id) => in_array($id, $allowed))->values()]);
    }

    public function create()
    {
        $permission = $this->permissions();
        $permissionGroups = PermissionPresenter::groups($permission);
        $branches = Department::where('status', 1)->get();
        return view('roles.create', compact('permission', 'permissionGroups', 'branches'));
    }

    private function authorizeTarget($id): Role
    {
        $role = BranchAccess::visibleRoles(request()->user())->findOrFail($id);
        abort_unless($this->canEdit($role), 403, 'Only SystemAdmin can modify shared roles.');
        return $role;
    }

    private function validatedPermissions(Request $request, ?int $branchId): array
    {
        $request->validate(['permission' => 'nullable|array', 'permission.*' => 'integer|exists:permissions,id']);
        $ids = array_values(array_unique($request->input('permission', [])));
        $allowed = $this->permissions()->pluck('id')->all();
        abort_unless(count(array_diff($ids, $allowed)) === 0, 403, 'Cannot grant permissions beyond your authority.');
        foreach (Permission::whereIn('id', $ids)->get() as $permission) {
            abort_unless($permission->guard_name === 'web', 422);
            if ($branchId && $branchId !== Department::HQ_ID) {
                $name = $permission->name;
                abort_if(in_array($name, array_merge(config('branch.central_permissions'), config('branch.owner_permissions')), true)
                    || BranchAccess::isCentralAffiliatePermission($name), 403, 'Central permissions cannot be assigned to branch roles.');
                $module = BranchAccess::moduleForPermission($name);
                abort_if($module && !BranchAccess::moduleEnabled($branchId, $module), 422, 'Enable the branch module before granting this permission.');
            }
        }
        return $ids;
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'branch_id' => 'nullable|integer|exists:departments,id', 'template_id' => 'nullable|integer|exists:roles,id']);
        $actor = $request->user();
        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : (BranchAccess::isOwner($actor) ? null : (int) $actor->department_id);
        if ($branchId) { BranchAccess::assertBranch($actor, $branchId, true); }
        abort_unless($branchId || BranchAccess::isOwner($actor), 403);
        abort_if(in_array(strtolower($data['name']), array_map('strtolower', config('branch.protected_roles')), true), 422, 'This role name is reserved.');
        if ($request->filled('template_id') && !$request->has('permission')) {
            $template = BranchAccess::visibleRoles($actor)->findOrFail($data['template_id']);
            $request->merge(['permission' => $template->permissions->pluck('id')->all()]);
        }
        $permissions = $this->validatedPermissions($request, $branchId);
        $name = $branchId ? 'branch-'.$branchId.'-'.Str::uuid() : $data['name'];
        $request->validate(['name' => [Rule::unique('roles', 'display_name')->where('branch_id', $branchId)]]);
        DB::transaction(function () use ($name, $data, $branchId, $permissions) {
            $role = Role::create(['name' => $name, 'display_name' => $data['name'], 'branch_id' => $branchId, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
            app(BranchContext::class)->audit('role-created', Role::class, $role->id, ['permissions' => $permissions, 'branch_id' => $branchId]);
        });
        return response()->json(['success' => 'Add Role Success']);
    }

    public function show($id)
    {
        $role = BranchAccess::visibleRoles(request()->user())->findOrFail($id);
        $rolePermissions = $role->permissions;
        return view('roles.show', compact('role', 'rolePermissions'));
    }

    public function edit($id)
    {
        $role = $this->authorizeTarget($id);
        return response()->json(['name' => $role->display_name ?: $role->name, 'permission' => $role->permissions->pluck('id'), 'branch_id' => $role->branch_id]);
    }

    public function update(Request $request, $id)
    {
        $role = $this->authorizeTarget($id);
        $data = $request->validate(['name' => 'required|string|max:100']);
        abort_if($role->name === 'SystemAdmin', 422, 'The owner role is protected.');
        $permissions = $this->validatedPermissions($request, $role->branch_id ? (int) $role->branch_id : null);
        DB::transaction(function () use ($role, $data, $permissions) {
            $role->display_name = $data['name'];
            $role->save();
            $role->syncPermissions($permissions);
            app(BranchContext::class)->audit('role-updated', Role::class, $role->id, ['permissions' => $permissions]);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return response()->json(['success' => 'Save Role Success']);
    }

    private function deleteRoles(array $ids): void
    {
        abort_unless($ids && count($ids) <= 500, 422);
        DB::transaction(function () use ($ids) {
            $roles = [];
            foreach (array_unique($ids) as $id) {
                $role = $this->authorizeTarget($id);
                abort_if(in_array($role->name, config('branch.protected_roles'), true), 422, 'Standard roles cannot be deleted.');
                abort_if(DB::table('model_has_roles')->where('role_id', $role->id)->exists(), 422, 'This role is assigned to users.');
                $roles[] = $role;
            }
            foreach ($roles as $role) {
                app(BranchContext::class)->audit('role-deleted', Role::class, $role->id);
                $role->delete();
            }
        });
    }

    public function destroy(Request $request)
    {
        $this->deleteRoles([(int) $request->id]);
        return response()->json(['success' => true, 'message' => 'Delete Role Success']);
    }

    public function destroy_all(Request $request)
    {
        $request->validate(['table_records' => 'required|array', 'table_records.*' => 'integer']);
        $this->deleteRoles($request->table_records);
        return redirect('/roles')->with('success', 'Delete Role Success');
    }
}
