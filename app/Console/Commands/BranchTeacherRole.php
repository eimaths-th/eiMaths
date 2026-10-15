<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use App\Support\Activity\ActivityRecorder;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\TeacherRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BranchTeacherRole extends Command
{
    protected $signature = 'branch:teacher-role {centreCode : Destination centre code} {--admin= : Same-branch user ID or email with user-create} {--apply : Create the reviewed scoped role; otherwise preview only}';
    protected $description = 'Preview or create a branch Teacher role within a designated administrator\'s current authority';

    public function handle(): int
    {
        try {
            $apply = (bool) $this->option('apply');
            $plan = $apply
                ? DB::transaction(fn (): array => $this->prepare(true), 3)
                : $this->prepare(false);

            $this->info($plan['display_name'].' — '.($apply ? ($plan['created'] ? 'created' : 'already matches; unchanged') : 'preview only'));
            $this->line('Branch ID: '.$plan['branch_id'].'; authority user ID: '.$plan['admin_id']);
            $this->line('Role: '.$plan['name']);
            $this->table(['Decision', 'Permission'], array_merge(
                array_map(fn (string $name): array => ['INCLUDE', $name], $plan['included']),
                array_map(fn (string $name): array => ['OMIT', $name], $plan['omitted']),
            ));
            $this->line('Existing roles and user assignments are unchanged.');
            if (!$apply) {
                $this->line('Run again with --apply to create this scoped role.');
            }
            if ($plan['created']) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }

            return self::SUCCESS;
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function prepare(bool $apply): array
    {
        $code = strtoupper(trim((string) $this->argument('centreCode')));
        $adminReference = trim((string) $this->option('admin'));
        if ($code === '' || $adminReference === '') {
            throw new \InvalidArgumentException('A centre code and --admin user ID or email are required.');
        }

        // Serialize provisioning for this branch. Every authority check is repeated
        // inside this transaction when applying, rather than trusting a prior preview.
        $branches = Department::query()->whereRaw('UPPER(TRIM(code)) = ?', [$code]);
        if ($apply) {
            $branches->lockForUpdate();
        }
        $branches = $branches->get();
        if ($branches->count() !== 1 || (int) $branches->first()->status !== 1
            || (int) $branches->first()->id === Department::HQ_ID) {
            throw new \InvalidArgumentException('Choose one active non-HQ centre with a unique code.');
        }
        $branch = $branches->first();

        $adminQuery = User::query();
        ctype_digit($adminReference) ? $adminQuery->whereKey($adminReference) : $adminQuery->where('email', $adminReference);
        if ($apply) {
            $adminQuery->lockForUpdate();
        }
        $admin = $adminQuery->first();
        if (!$admin || (int) $admin->disable !== 0 || (int) $admin->department_id !== (int) $branch->id
            || $admin->hasAnyRole(['SystemAdmin', 'Admin']) || !$admin->checkPermissionTo('user-create')) {
            throw new \InvalidArgumentException('The designated administrator must be active in this centre and have user-create.');
        }

        $templateQuery = Role::query()->where('name', 'Teacher')->where('guard_name', 'web')->whereNull('branch_id');
        if ($apply) {
            $templateQuery->lockForUpdate();
        }
        $template = $templateQuery->first();
        if (!$template) {
            throw new \InvalidArgumentException('The shared web Teacher role is missing.');
        }
        $authority = $admin->getAllPermissions()->pluck('name')->all();
        $permissions = $template->permissions
            ->filter(fn ($permission): bool => $permission->guard_name === 'web'
                && in_array($permission->name, $authority, true)
                && BranchAccess::permissionAllowed($admin, $permission->name))
            ->sortBy('name')->values();
        $included = $permissions->pluck('name')->all();
        $omitted = $template->permissions->pluck('name')->diff($included)->sort()->values()->all();
        if ($permissions->isEmpty()) {
            throw new \InvalidArgumentException('No Teacher permissions are currently delegable by this administrator; no role was created.');
        }

        $name = TeacherRole::scopedName((int) $branch->id);
        $displayName = 'Teacher ('.$code.')';
        $existingQuery = Role::query()->where('name', $name);
        if ($apply) {
            $existingQuery->lockForUpdate();
        }
        $existing = $existingQuery->get();
        if ($existing->isNotEmpty()) {
            $role = $existing->first();
            $expectedIds = $permissions->pluck('id')->sort()->values()->all();
            $actualIds = $role->permissions->pluck('id')->sort()->values()->all();
            if ($existing->count() !== 1 || $role->guard_name !== 'web'
                || (int) $role->branch_id !== (int) $branch->id || $role->display_name !== $displayName
                || $actualIds !== $expectedIds) {
                throw new \InvalidArgumentException('An existing scoped Teacher role differs from this plan. Review it manually; no roles or users were changed.');
            }
        } elseif (Role::query()->where('branch_id', $branch->id)->where('display_name', $displayName)->exists()) {
            throw new \InvalidArgumentException('Another role already uses this branch Teacher display name. Review it manually; nothing was changed.');
        }

        $created = false;
        if ($apply && $existing->isEmpty()) {
            $role = Role::create(['name' => $name, 'display_name' => $displayName, 'branch_id' => $branch->id, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
            app(ActivityRecorder::class)->write([
                'category' => 'activity', 'module' => 'users', 'action' => 'teacher-role.provisioned',
                'subject_type' => Role::class, 'subject_id' => $role->id, 'branch_id' => $branch->id,
                'after_values' => ['name' => $name, 'display_name' => $displayName, 'permissions' => $included],
                'metadata' => ['source' => 'console', 'authority_user_id' => $admin->id],
            ], null, false);
            $created = true;
        }

        return ['name' => $name, 'display_name' => $displayName, 'branch_id' => (int) $branch->id,
            'admin_id' => (int) $admin->id, 'included' => $included, 'omitted' => $omitted, 'created' => $created];
    }
}
