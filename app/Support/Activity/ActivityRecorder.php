<?php

namespace App\Support\Activity;

use App\Models\User;
use App\Support\Branch\BranchContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ActivityRecorder
{
    public function rules(string $class): ?array
    {
        return config('activity-log.models', [])[$class] ?? null;
    }

    public function module(?Request $request = null): ?string
    {
        $class = ($request ?? request())->route()?->getControllerClass();
        return config('activity-log.controllers', [])[class_basename($class ?? '')] ?? null;
    }

    public function enabled(): bool
    {
        return app(BranchContext::class)->actor !== null
            && (request()->route() === null || $this->module() !== null);
    }

    public function branch(Model $model): ?int
    {
        $rule = $this->rules(get_class($model));
        if (isset($rule['branch'])) {
            return $model->getAttribute($rule['branch']) ?: null;
        }
        if (isset($rule['parent'])) {
            [$parent, $key] = $rule['parent'];
            $record = $parent::withoutGlobalScopes()->find($model->getAttribute($key));
            return $record ? $this->branch($record) : null;
        }
        return null; // Shared roles and permissions belong to central administration.
    }

    public function protectedSubject(Model $model): bool
    {
        return ($model instanceof User && ($model->hasRole('SystemAdmin', 'web')
                || (class_basename(request()->route()?->getControllerClass() ?? '') === 'UserController'
                    && request()->input('role') === 'SystemAdmin')))
            || ($model instanceof \Spatie\Permission\Models\Role && $model->name === 'SystemAdmin');
    }

    public function modelChange(Model $model, string $action, ?array $snapshot = null): void
    {
        if (!$this->enabled() || !($rule = $this->rules(get_class($model)))) {
            return;
        }
        $fields = array_flip($rule['fields']);
        $before = $action === 'created' ? [] : array_intersect_key($model->getRawOriginal(), $fields);
        $after = $action === 'deleted' ? [] : array_intersect_key($model->getAttributes(), $fields);
        if ($action === 'updated') {
            $changed = array_intersect_key($fields, $model->getChanges());
            $before = array_intersect_key($before, $changed);
            $after = array_intersect_key($after, $changed);
        }
        $passwordChanged = $action === 'updated' && $model->wasChanged('password');
        if ($action === 'updated' && !$before && !$after && !$passwordChanged) {
            return;
        }
        $branch = $snapshot['branch'] ?? $this->branch($model);
        $previousBranch = isset($rule['branch']) ? ($before[$rule['branch']] ?? $branch) : $branch;
        $transferred = $action === 'updated' && $previousBranch && $branch && (int) $previousBranch !== (int) $branch;
        $this->write([
            'category' => 'activity', 'module' => $rule['module'],
            'action' => $transferred ? 'branch.transferred' : $action,
            'subject_type' => get_class($model), 'subject_id' => $model->getKey(),
            'subject_is_owner' => $snapshot['protected'] ?? $this->protectedSubject($model),
            'branch_id' => $branch, 'related_branch_id' => $transferred ? $previousBranch : null,
            'before_values' => $before, 'after_values' => $after,
            'metadata' => array_filter([
                'password_changed' => $passwordChanged ?: null,
                'reason' => $transferred ? mb_substr((string) request()->input('reason', ''), 0, 500) : null,
            ], fn ($value) => $value !== null),
        ]);
    }

    public function assignments(Model $model, string $key, array $before, array $after): void
    {
        sort($before); sort($after);
        if (!$this->enabled() || $before === $after) {
            return;
        }
        $this->write([
            'category' => 'activity', 'module' => 'users', 'action' => $key.'.changed',
            'subject_type' => get_class($model), 'subject_id' => $model->getKey(),
            'subject_is_owner' => $this->protectedSubject($model) || in_array('SystemAdmin', $before, true),
            'branch_id' => $this->branch($model),
            'before_values' => [$key => $before], 'after_values' => [$key => $after],
        ]);
    }

    public function failure(Request $request, int $status): void
    {
        if (!$this->module($request) || !app(BranchContext::class)->actor) {
            return;
        }
        // No target IDs or request bodies: rejected requests must not disclose another branch.
        $this->write([
            'category' => 'activity', 'module' => $this->module($request),
            'action' => 'request.failed', 'outcome' => $status === 403 ? 'denied' : 'failed',
            'subject_type' => 'request', 'branch_id' => app(BranchContext::class)->actor->department_id,
            'metadata' => ['route' => $request->route()?->getName(), 'status' => $status],
        ]);
    }

    public function write(array $data, ?User $actor = null, bool $useContext = true): void
    {
        $actor ??= $useContext ? app(BranchContext::class)->actor : null;
        $request = request();
        $data += [
            'actor_id' => $actor?->id, 'actor_name' => $actor?->name,
            'actor_branch_id' => $actor?->department_id,
            'actor_is_owner' => $actor?->hasRole('SystemAdmin', 'web') ?? false,
            'outcome' => 'success', 'created_at' => now(),
            'ip_address' => mb_substr((string) $request->ip(), 0, 45),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ];
        foreach (['before_values', 'after_values', 'metadata'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = json_encode($data[$key], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
            }
        }
        DB::table('branch_audit_logs')->insert($data);
    }
}
