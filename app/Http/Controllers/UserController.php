<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show', 'find', 'affiliateUser']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy', 'destroy_all', 'destroyAffiliateUser']]);
    }

    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    public function affiliateUser(Request $request)
    {
        return $this->listing($request, true);
    }

    private function listing(Request $request, bool $affiliate)
    {
        $roles = BranchAccess::visibleRoles($request->user())->get()->mapWithKeys(fn ($role) => [$role->name => $role->display_name ?: $role->name])->all();
        if (!$request->ajax()) {
            $department = Department::where('status', 1)->orderBy('name')->get();
            $position = Position::where('status', 1)->orderBy('name')->get();
            return view($affiliate ? 'users.affiliate_user' : 'users.index', compact('roles', 'department', 'position'));
        }
        $query = User::with(['department', 'position', 'roles']);
        $affiliate ? $query->where('affiliate_request', 1) : $query->where(fn ($q) => $q->whereNull('affiliate_request')->orWhere('affiliate_request', '!=', 1));
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }
        // The existing UI uses 1 as the "all branches" filter value.
        if ($request->filled('centre') && (string) $request->centre !== '1') {
            $query->where('department_id', $request->centre);
        }
        if (is_string($request->date_range) && preg_match('/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/', $request->date_range)) {
            [$from, $to] = explode(' - ', $request->date_range);
            $query->whereBetween('users.created_at', [$from, $to.' 23:59:59']);
        }
        $years = array_values(array_filter((array) $request->input('years', []), fn ($year) => preg_match('/^\d{4}$/', (string) $year)));
        if ($years) {
            $query->where(function ($q) use ($years) { foreach ($years as $year) { $q->orWhereYear('users.created_at', $year); } });
        }
        return datatables()->of($query)
            ->orderColumn('department', function ($q, $order) {
                $q->orderBy(
                    Department::query()->select('name')
                        ->whereColumn('departments.id', 'users.department_id')->limit(1),
                    $order
                );
            })
            ->orderColumn('position', function ($q, $order) {
                $q->orderBy(
                    Position::query()->select('name')
                        ->whereColumn('positions.id', 'users.position_id')->limit(1),
                    $order
                );
            })
            ->orderColumn('role', function ($q, $order) {
                $q->orderBy(
                    Role::query()->selectRaw('MIN(COALESCE(roles.display_name, roles.name))')
                        ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.model_type', (new User)->getMorphClass())
                        ->where('roles.guard_name', 'web'),
                    $order
                );
            })
            ->filterColumn('department', fn ($q, $keyword) => $q->whereHas('department', fn ($d) => $d->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('position', fn ($q, $keyword) => $q->whereHas('position', fn ($d) => $d->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('role', fn ($q, $keyword) => $q->whereHas('roles', fn ($d) => $d->where(fn ($role) => $role->where('name', 'like', "%{$keyword}%")->orWhere('display_name', 'like', "%{$keyword}%"))))
            ->editColumn('role', fn (User $row) => $row->roles->map(fn ($role) => $role->display_name ?: $role->name)->implode(', '))
            ->editColumn('disable', fn ($row) => $row->disable == 1 ? 'Yes' : 'No')
            ->editColumn('department', fn ($row) => $row->department?->name ?? '-')
            ->editColumn('position', fn ($row) => $row->position?->name ?? '-')
            ->editColumn('checkbox', fn ($row) => '<input type="checkbox" name="table_records[]" value="'.(int) $row->id.'">')
            ->addColumn('action', function ($row) use ($request, $affiliate) {
                if (!BranchAccess::isOwner($request->user()) && $row->hasRole('Admin')) {
                    return '';
                }
                $html = $request->user()->can('user-edit') ? '<button class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="'.(int) $row->id.'">Edit</button> ' : '';
                if ($request->user()->can('user-delete')) {
                    $html .= '<button class="btn btn-sm btn-danger '.($affiliate ? 'btn-delete-affiliate-user' : 'btn-delete').'" data-rowid="'.(int) $row->id.'">Delete</button>';
                }
                return $html;
            })->rawColumns(['checkbox', 'action'])->toJson();
    }

    public function create()
    {
        $roles = BranchAccess::visibleRoles(request()->user())->get()->mapWithKeys(fn ($role) => [$role->name => $role->display_name ?: $role->name])->all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', 'department_id' => 'required|integer',
            'position_id' => 'nullable|integer', 'role' => 'required|string', 'disable' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }
        $data = $validator->validated();
        BranchAccess::assertBranch($request->user(), $data['department_id'], true);
        $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();
        BranchAccess::assertAssignableRole($request->user(), $role, (int) $data['department_id']);
        DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']),
                'department_id' => $data['department_id'], 'position_id' => $data['position_id'] ?? null, 'disable' => $data['disable'] ?? 0,
            ]);
            $user->assignRole($role);
            app(BranchContext::class)->audit('role-assigned', User::class, $user->id, ['role_id' => $role->id]);
        });
        return response()->json(['success' => 'Add User Success']);
    }

    public function find($type, $position)
    {
        $users = User::where('position_id', $position)->orderBy('name')->get();
        $html = "<option value=''>Please select</option>";
        foreach ($users as $user) {
            $html .= '<option value="'.(int) $user->id.'">'.e($user->name).'</option>';
        }
        return response()->json(['html' => $html]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);
        BranchAccess::assertManageUser(request()->user(), $data);
        $html = '';
        foreach (BranchAccess::visibleRoles(request()->user())->get() as $role) {
            $html .= '<option value="'.e($role->name).'" '.($data->hasRole($role) ? 'selected' : '').'>'.e($role->display_name ?: $role->name).'</option>';
        }
        $position = $data->position;
        $positionHtml = $position ? '<option value="'.(int) $position->id.'">'.e($position->name).'</option>' : '<option value="">ไม่มีแผนก</option>';
        return response()->json(['data' => $data, 'html' => $html, 'select_list_position' => $positionHtml]);
    }

    public function update(Request $request, $id)
    {
        $target = User::withoutGlobalScopes()->findOrFail($id);
        BranchAccess::assertManageUser($request->user(), $target);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($target->id)],
            'department' => 'required|integer', 'position' => 'nullable|integer', 'role' => 'required|string',
            'password' => 'nullable|string|min:8|confirmed', 'disable' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }
        $data = $validator->validated();
        BranchAccess::assertBranch($request->user(), $data['department']);
        abort_unless((int) $target->department_id === (int) $data['department'], 422, 'Use the branch transfer workflow.');
        $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();
        BranchAccess::assertAssignableRole($request->user(), $role, (int) $data['department']);
        DB::transaction(function () use ($target, $data, $role) {
            // Serialize owner changes before locking an individual user.
            $targetWasOwner = $target->hasRole('SystemAdmin');
            if ($targetWasOwner && ($role->name !== 'SystemAdmin' || (int) $data['disable'] === 1)) {
                BranchAccess::assertOwnerRemains([$target->id]);
            }
            $target->fill(['name' => $data['name'], 'email' => $data['email'], 'position_id' => $data['position'], 'disable' => $data['disable']]);
            if (!empty($data['password'])) {
                $target->password = Hash::make($data['password']);
            }
            $target->save();
            $target->syncRoles([$role]);
            app(BranchContext::class)->audit('role-assigned', User::class, $target->id, ['role_id' => $role->id]);
        });
        return response()->json(['success' => 'Save User Success']);
    }

    private function deleteUsers(Request $request, array $ids): void
    {
        $ids = array_values(array_unique($ids));
        abort_unless($ids && count($ids) <= 500, 422);
        DB::transaction(function () use ($ids, $request) {
            $users = User::withoutGlobalScopes()->whereIn('id', $ids)->get();
            abort_unless($users->count() === count($ids), 404);
            foreach ($users as $user) {
                BranchAccess::assertManageUser($request->user(), $user);
            }
            if ($users->contains(fn ($user) => $user->hasRole('SystemAdmin'))) {
                BranchAccess::assertOwnerRemains($ids);
            }
            foreach ($users as $user) {
                $user->delete();
            }
        });
    }

    public function destroy(Request $request)
    {
        $this->deleteUsers($request, [(int) $request->id]);
        return response()->json(['success' => true, 'message' => 'Delete User Success']);
    }

    public function destroyAffiliateUser(Request $request)
    {
        return $this->destroy($request);
    }

    public function destroy_all(Request $request)
    {
        $request->validate(['table_records' => 'required|array', 'table_records.*' => 'required|integer']);
        $this->deleteUsers($request, $request->table_records);
        return redirect('/users')->with('success', 'Delete User Success');
    }

    public function perform()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('login');
    }
}
