<?php

namespace App\Support\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class BranchContext
{
    public ?User $actor = null;
    public bool $allowTransfer = false;
    public array $moduleBranches = [];

    public function runAs(User $actor, callable $callback): mixed
    {
        $previous = $this->actor;
        $previousModules = $this->moduleBranches;
        $this->actor = $actor;
        $this->moduleBranches = [];
        try {
            return $callback();
        } finally {
            $this->actor = $previous;
            $this->moduleBranches = $previousModules;
        }
    }

    public function scope(Builder $query): void
    {
        if (!$this->actor) {
            return;
        }
        $model = $query->getModel();
        if ($model instanceof User && !BranchAccess::isOwner($this->actor)) {
            $query->whereNotExists(function ($q) use ($model) {
                $q->selectRaw('1')->from('model_has_roles as branch_hidden_members')
                    ->join('roles as branch_hidden_roles', 'branch_hidden_roles.id', '=', 'branch_hidden_members.role_id')
                    ->whereColumn('branch_hidden_members.model_id', $model->qualifyColumn('id'))
                    ->where('branch_hidden_members.model_type', $model->getMorphClass())
                    ->where('branch_hidden_roles.name', 'SystemAdmin');
            });
        }
        $rule = config('branch.models.'.get_class($model));
        if (!$rule) {
            return;
        }
        if (($rule['special'] ?? null) === 'history-image') {
            $query->where(function ($q) use ($model) {
                $q->whereIn($model->qualifyColumn('history_id'), \App\Models\Histrories::query()->select('histrories.id'))
                    ->orWhere(function ($loose) use ($model) {
                        $loose->whereNull($model->qualifyColumn('history_id'))
                            ->whereIn($model->qualifyColumn('student_id'), Contact::query()->select('contacts.id'));
                    });
            });
            return;
        }
        if (($rule['special'] ?? null) === 'feedback') {
            if (!BranchAccess::isCentral($this->actor) || !$this->actor->can('support-manage')) {
                $query->where($model->qualifyColumn('user_id'), $this->actor->id);
            }
            return;
        }
        if (($rule['special'] ?? null) === 'test') {
            $query->where(function ($q) use ($model) {
                $q->where(function ($students) use ($model) {
                    $students->where($model->qualifyColumn('type_user'), 'student')
                        ->whereIn($model->qualifyColumn('user_id'), Contact::query()->select('contacts.id'));
                })->orWhere(function ($users) use ($model) {
                    $users->where($model->qualifyColumn('type_user'), 'user')
                        ->whereIn($model->qualifyColumn('user_id'), User::query()->select('users.id'));
                });
            });
            return;
        }
        if ($model instanceof \App\Models\FileStore) {
            $query->where($model->qualifyColumn('module'), 'orders');
        }
        if (isset($rule['owner'])) {
            $query->where($model->qualifyColumn($rule['owner']), $this->actor->id);
        }
        if (BranchAccess::canCrossBranches($this->actor)) {
            return;
        }
        if (isset($rule['column'])) {
            $query->where($model->qualifyColumn($rule['column']), $this->actor->department_id ?: -1);
        } else {
            $parent = new $rule['parent'];
            $query->whereIn($model->qualifyColumn($rule['key']), $parent->newQuery()->select($parent->qualifyColumn('id')));
        }
    }

    public function validateWrite(Model $model): void
    {
        if (!$this->actor) {
            return;
        }
        $rule = config('branch.models.'.get_class($model));
        if (!$rule) {
            return;
        }
        if (($rule['special'] ?? null) === 'history-image') {
            if ($model->history_id) {
                $history = \App\Models\Histrories::query()->find($model->history_id);
                abort_unless($history && (int) $history->student_id === (int) $model->student_id, 403, 'Image and lesson must belong to the same student.');
            } else {
                abort_unless(Contact::query()->whereKey($model->student_id)->exists(), 403);
            }
            return;
        }
        if ($model instanceof \App\Models\Parents && $model->exists && !BranchAccess::canCrossBranches($this->actor)) {
            $hasOtherBranchChildren = DB::table('parent_student')->join('contacts', 'contacts.id', '=', 'parent_student.student_id')
                ->where('parent_student.parent_id', $model->id)
                ->where('contacts.centre', '!=', $this->actor->department_id)->exists();
            abort_if($hasOtherBranchChildren, 403, 'Central administration must manage a parent account shared by multiple branches.');
        }
        if (($rule['special'] ?? null) === 'feedback') {
            if (!$model->exists) {
                $model->user_id = $this->actor->id;
            } else {
                abort_unless((int) $model->user_id === (int) $this->actor->id || (BranchAccess::isCentral($this->actor) && $this->actor->can('support-manage')), 403);
            }
            return;
        }
        if (($rule['special'] ?? null) === 'test') {
            $class = $model->type_user === 'student' ? Contact::class : User::class;
            abort_unless($class::query()->whereKey($model->user_id)->exists(), 403);
            return;
        }
        if ($model instanceof \App\Models\FileStore) {
            abort_unless($model->module === 'orders', 403);
        }
        if (isset($rule['owner'])) {
            if (!$model->exists) {
                $model->setAttribute($rule['owner'], $this->actor->id);
            }
            abort_unless((int) $model->getAttribute($rule['owner']) === (int) $this->actor->id, 403);
        }
        if ($model instanceof User && $model->exists) {
            $original = User::withoutGlobalScopes()->findOrFail($model->id);
            // Profile edits of the current account still cannot change its branch.
            if ((int) $original->id !== (int) $this->actor->id || $model->isDirty(['department_id', 'disable'])) {
                BranchAccess::assertManageUser($this->actor, $original);
            }
            if ($original->hasRole('SystemAdmin') && $model->isDirty('disable') && (int) $model->disable === 1) {
                BranchAccess::assertOwnerRemains([$model->id]);
            }
        }
        if (isset($rule['column'])) {
            $column = $rule['column'];
            if ($model instanceof Department && !$model->exists) {
                abort_unless(BranchAccess::isCentral($this->actor), 403);
                return;
            }
            if (!$model->exists && $model->getAttribute($column) === null) {
                $model->setAttribute($column, $this->actor->department_id);
            }
            $branch = $model->getAttribute($column);
            if ($column === 'branch_id' && $branch === null && BranchAccess::isCentral($this->actor)
                && $model instanceof \App\Models\CrmLead) {
                abort_if($model->assigned_staff_id, 422, 'Assign the lead to a branch before assigning staff.');
                return;
            }
            BranchAccess::assertBranch($this->actor, $branch, !$model->exists);
            if ($model->exists && $model->isDirty($column) && !$this->allowTransfer) {
                abort(422, 'Use the branch transfer workflow to change a record branch.');
            }
            if ($model->exists && $model->getRawOriginal($column) !== null) {
                BranchAccess::assertBranch($this->actor, $model->getRawOriginal($column));
            }
            $this->validateRelations($model, (int) $branch);
        } else {
            $parent = $rule['parent']::query()->find($model->getAttribute($rule['key']));
            abort_unless($parent, 403, 'Related record is outside your branch.');
        }
    }

    private function validateRelations(Model $model, int $branch): void
    {
        $table = $model->getTable();
        $references = match ($table) {
            'users' => ['position_id' => ['positions', 'department_id']],
            'histrories' => ['student_id' => ['contacts', 'centre'], 'teacher_id' => ['users', 'department_id']],
            'orders', 'receipts', 'invoices' => ['cid' => ['contacts', 'centre']],
            'stocks' => ['product_id' => ['products', 'centre'], 'student_id' => ['contacts', 'centre']],
            'crm_leads' => ['assigned_staff_id' => ['users', 'department_id']],
            default => [],
        };
        foreach ($references as $key => [$relatedTable, $column]) {
            $id = $model->getAttribute($key);
            if ($id === null || $id === '' || (int) $id === 0 || ($model->exists && !$model->isDirty($key))) {
                continue;
            }
            $related = $relatedTable === 'users' ? User::query() : DB::table($relatedTable);
            abort_unless($related->where('id', $id)->where($column, $branch)->exists(), 422, 'Related record must belong to the same branch.');
        }
        if ($table === 'parents' && (!$model->exists || $model->isDirty('student_id'))) {
            $ids = array_values(array_unique(array_filter(explode(',', (string) $model->student_id))));
            abort_unless(count($ids) === Contact::query()->where('centre', $branch)->whereIn('id', $ids)->count(), 422, 'Parent students must belong to the selected branch.');
        }
    }

    public function audit(string $action, string $type, $id, array $changes = []): void
    {
        if (!$this->actor) {
            return;
        }
        DB::table('branch_audit_logs')->insert([
            'actor_id' => $this->actor->id, 'branch_id' => $this->actor->department_id,
            'action' => $action, 'subject_type' => $type, 'subject_id' => $id,
            'changes' => json_encode(array_diff_key($changes, array_flip(['password', 'remember_token', 'token', 'channel_secret', 'channel_access_token']))),
            'created_at' => now(),
        ]);
    }
}
