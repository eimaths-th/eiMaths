<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Branch\BranchAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    private array $branchNames = [];
    private ?array $ownerIdsCache = null;

    private function branchName(int $id): string
    {
        return $this->branchNames[$id] ??= (DB::table('departments')->where('id', $id)->value('name') ?? '#'.$id);
    }

    private function ownerReference($id): bool
    {
        $this->ownerIdsCache ??= array_map('intval', $this->ownerIds()->pluck('mr.model_id')->all());
        return $id !== null && in_array((int) $id, $this->ownerIdsCache, true);
    }

    public function __construct()
    {
        $this->middleware('permission:activity-log-list');
    }

    private function ownerIds()
    {
        return DB::table('model_has_roles as mr')->join('roles as r', 'r.id', '=', 'mr.role_id')
            ->where('mr.model_type', (new User)->getMorphClass())->where('r.guard_name', 'web')
            ->where('r.name', 'SystemAdmin')->select('mr.model_id');
    }

    private function visible(User $actor)
    {
        $query = DB::table('branch_audit_logs')->whereIn('category', ['authentication', 'activity']);
        if (!BranchAccess::canCrossBranches($actor)) {
            $query->where(fn ($q) => $q->where('branch_id', $actor->department_id)
                ->orWhere('related_branch_id', $actor->department_id));
        }
        if (!BranchAccess::isOwner($actor)) {
            // Protect the whole event using both its recorded role and the account's current role.
            $query->where('actor_is_owner', false)
                ->where(fn ($q) => $q->whereNull('actor_id')->orWhereNotIn('actor_id', $this->ownerIds()))
                ->where('subject_is_owner', false)
                ->where(fn ($q) => $q->where('subject_type', '!=', User::class)->orWhereNull('subject_id')
                    ->orWhereNotIn('subject_id', $this->ownerIds()));
        }
        return $query;
    }

    private function present(object $row, User $actor): array
    {
        $hiddenActor = !BranchAccess::isOwner($actor) && ($row->actor_is_owner
            || $this->ownerReference($row->actor_id));
        $before = json_decode($row->before_values ?? '{}', true) ?: [];
        $after = json_decode($row->after_values ?? '{}', true) ?: [];
        if (!BranchAccess::isOwner($actor)) {
            foreach (['before', 'after'] as $side) {
                if (isset(${$side}['teacher_id']) && ($this->ownerReference(${$side}['teacher_id'])
                    || ($hiddenActor && (int) ${$side}['teacher_id'] === (int) $row->actor_id))) {
                    ${$side}['teacher_id'] = __('activity-log.central_actor');
                }
            }
        }
        return [
            'id' => $row->id, 'created_at' => CarbonImmutable::parse($row->created_at, config('app.timezone'))->setTimezone('Asia/Bangkok')->format('d/m/Y H:i:s'),
            'actor' => $hiddenActor ? __('activity-log.central_actor') : ($row->actor_name ?: __('activity-log.unidentified')),
            'actor_id' => $hiddenActor ? null : $row->actor_id,
            'branch' => $row->branch_id ? $this->branchName($row->branch_id) : __('activity-log.central_scope'),
            'related_branch' => $row->related_branch_id ? $this->branchName($row->related_branch_id) : null,
            'category' => $row->category, 'module' => $row->module, 'action' => $row->action, 'outcome' => $row->outcome,
            'subject' => __('activity-log.subjects.'.class_basename($row->subject_type)),
            'subject_id' => $row->subject_id,
            'before' => $before, 'after' => $after,
            'metadata' => json_decode($row->metadata ?? '{}', true) ?: [],
            'ip' => $hiddenActor ? null : $row->ip_address,
            'browser' => $hiddenActor ? null : $row->user_agent,
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', Rule::in(['authentication', 'activity'])],
            'from' => 'nullable|date_format:Y-m-d',
            'to' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('from') ? 'after_or_equal:from' : null]),
            'branch_id' => 'nullable|integer|min:1', 'actor_id' => 'nullable|integer|min:1',
            'module' => ['nullable', Rule::in(['authentication', 'users', 'branches', 'students', 'teaching', 'finance'])],
            'outcome' => ['nullable', Rule::in(['success', 'failed', 'denied'])],
            'action' => ['nullable', Rule::in(['created', 'updated', 'deleted', 'roles.changed', 'permissions.changed', 'branch.transferred', 'request.failed', 'login', 'logout', 'login.failed', 'login.denied'])],
        ]);
        $actor = $request->user();
        $crossBranch = BranchAccess::canCrossBranches($actor);
        if (!empty($filters['branch_id'])) {
            BranchAccess::assertBranch($actor, $filters['branch_id']);
        }
        $category = $filters['category'] ?? 'authentication';
        $query = $this->visible($actor)->where('category', $category);
        foreach (['module', 'outcome', 'action'] as $field) {
            if (!empty($filters[$field])) { $query->where($field, $filters[$field]); }
        }
        if (!empty($filters['branch_id'])) {
            $query->where(fn ($q) => $q->where('branch_id', $filters['branch_id'])->orWhere('related_branch_id', $filters['branch_id']));
        }
        if (!empty($filters['actor_id'])) {
            $query->where('actor_id', $filters['actor_id']);
        }
        foreach (['from' => '>=', 'to' => '<'] as $field => $operator) {
            if (!empty($filters[$field])) {
                $date = CarbonImmutable::parse($filters[$field], 'Asia/Bangkok')->startOfDay();
                if ($field === 'to') { $date = $date->addDay(); }
                $query->where('created_at', $operator, $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'));
            }
        }
        $logs = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $logs->setCollection($logs->getCollection()->map(fn ($row) => $this->present($row, $actor)));
        $actorsQuery = $this->visible($actor)->whereNotNull('actor_id');
        $actors = $actorsQuery->select('actor_id')->selectRaw('MAX(actor_name) as name')->groupBy('actor_id')->orderBy('name')->get();
        $branches = $crossBranch ? DB::table('departments')->orderBy('name')->get(['id', 'name']) : collect();
        return view('activity-logs.index', compact('logs', 'actors', 'branches', 'crossBranch', 'filters', 'category'));
    }

    public function show(Request $request, int $id)
    {
        $row = $this->visible($request->user())->where('id', $id)->first();
        abort_unless($row, 404);
        $log = $this->present($row, $request->user());
        return view('activity-logs.show', compact('log'));
    }
}
