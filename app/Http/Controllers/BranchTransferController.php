<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:branch-transfer']);
    }

    public function index(Request $request)
    {
        abort_unless(BranchAccess::isCentral($request->user()), 403);
        $branches = Department::where('status', 1)->orderBy('name')->get();
        $positions = \App\Models\Position::where('status', 1)->get(['id', 'name', 'department_id']);
        return view('branches.transfers', compact('branches', 'positions'));
    }

    public function targets(Request $request)
    {
        abort_unless(BranchAccess::isCentral($request->user()), 403);
        $data = $request->validate(['type' => 'required|in:user,student', 'q' => 'required|string|min:2|max:100']);
        $query = $data['type'] === 'user'
            ? User::query()->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['SystemAdmin', 'Admin']))
            : Contact::query();
        $column = $data['type'] === 'user' ? 'department_id' : 'centre';
        $records = $query->where(function ($q) use ($data) {
            $q->where('name', 'like', '%'.$data['q'].'%');
            if ($data['type'] === 'student') { $q->orWhere('code', 'like', '%'.$data['q'].'%'); }
        })->orderBy('name')->limit(30)->get();
        $branches = Department::whereIn('id', $records->pluck($column))->pluck('name', 'id');
        return response()->json(['data' => $records->map(fn ($record) => [
            'id' => $record->id,
            'label' => $record->name.($data['type'] === 'student' ? ' ('.$record->code.')' : '').' — '.($branches[$record->$column] ?? ''),
        ])]);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless(BranchAccess::isCentral($actor), 403);
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
        $result = DB::transaction(function () use ($actor, $data) {
            $class = $data['type'] === 'user' ? User::class : Contact::class;
            $record = $class::query()->lockForUpdate()->findOrFail($data['record_id']);
            $column = $data['type'] === 'user' ? 'department_id' : 'centre';
            $source = (int) $record->getAttribute($column);
            BranchAccess::assertBranch($actor, $source);
            abort_if($source === (int) $data['to_branch_id'], 422, __('centre-management.transfers.errors.different_destination'));
            if ($record instanceof User) {
                BranchAccess::assertManageUser($actor, $record);
                abort_if($record->hasAnyRole(['SystemAdmin', 'Admin']), 422, 'Central administrators must remain in HQ.');
                foreach ($record->roles as $role) {
                    BranchAccess::assertAssignableRole($actor, $role, (int) $data['to_branch_id']);
                }
                $record->position_id = $data['position_id'] ?? null;
            } else {
                // An issued document and its branch must not be rewritten by a transfer.
                // Finish the current enrolment first; transferring active billing requires
                // a separate credit/settlement operation with the existing finance guards.
                $open = DB::table('orders')->where('cid', $record->id)->where('centre', $source)
                    ->where(fn ($q) => $q->whereNull('status')->orWhereIn('status', ['0', '1']))->exists();
                abort_if($open, 422, __('centre-management.transfers.errors.pending_orders'));
                $record->order = 0; // New enrolments use a new order at the destination.
            }
            $context = app(BranchContext::class);
            $previous = $context->allowTransfer;
            try {
                $context->allowTransfer = true;
                $record->setAttribute($column, $data['to_branch_id']);
                $record->save();
            } finally {
                $context->allowTransfer = $previous;
            }
            DB::table('branch_transfers')->insert([
                'subject_type' => $class, 'subject_id' => $record->id,
                'from_branch_id' => $source, 'to_branch_id' => $data['to_branch_id'],
                'actor_id' => $actor->id, 'reason' => $data['reason'], 'created_at' => now(),
            ]);
            if ($record instanceof User) {
                $record->tokens()->delete();
            }
            return ['id' => $record->id, 'from_branch_id' => $source, 'to_branch_id' => (int) $data['to_branch_id']];
        });
        return $request->expectsJson()
            ? response()->json(['success' => true, 'data' => $result])
            : back()->with('success', __('centre-management.transfers.messages.success'));
    }
}
