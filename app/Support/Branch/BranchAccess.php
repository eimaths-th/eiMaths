<?php

namespace App\Support\Branch;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class BranchAccess
{
    public static function isOwner(User $actor): bool
    {
        return $actor->isHq() && $actor->hasRole('SystemAdmin', 'web');
    }

    public static function isCentral(User $actor): bool
    {
        return self::isOwner($actor) || ($actor->isHq() && $actor->hasRole('Admin', 'web'));
    }

    public static function canCrossBranches(User $actor): bool
    {
        return self::isOwner($actor) || (self::isCentral($actor) && $actor->checkPermissionTo('all-centre'));
    }

    public static function moduleForPermission(string $permission): ?string
    {
        foreach (config('branch.permission_modules', []) as $prefix => $module) {
            if (str_starts_with(strtolower($permission), $prefix)) {
                return $module;
            }
        }
        return null;
    }

    public static function moduleEnabled(?int $branchId, string $module): bool
    {
        if (!$branchId) {
            return false;
        }
        $context = app(BranchContext::class);
        $branch = $context->actor
            ? ($context->moduleBranches[$branchId] ??= DB::table('departments')->where('id', $branchId)->first(['enabled_modules', 'status']))
            : DB::table('departments')->where('id', $branchId)->first(['enabled_modules', 'status']);
        if (!$branch || (int) $branch->status !== 1) {
            return false;
        }
        // NULL is the explicit compatibility state for existing branches.
        $modules = $branch->enabled_modules === null ? array_keys(config('branch.modules')) : json_decode($branch->enabled_modules, true);
        return is_array($modules) && in_array($module, $modules, true);
    }

    public static function isCentralAffiliatePermission(string $permission): bool
    {
        $permission = strtolower($permission);
        if (!str_starts_with($permission, 'affiliate-') || $permission === 'affiliate-user') {
            return false;
        }
        foreach (config('branch.branch_scoped_affiliate_permission_prefixes', []) as $prefix) {
            if (str_starts_with($permission, strtolower($prefix))) {
                return false;
            }
        }
        return true;
    }

    public static function permissionAllowed(User $actor, string $permission): bool
    {
        if ((int) $actor->disable === 1) {
            return false;
        }
        if (self::isOwner($actor)) {
            return true;
        }
        $permission = strtolower($permission);
        if (in_array($permission, config('branch.owner_permissions'), true)) {
            return false;
        }
        if (self::isCentralAffiliatePermission($permission) && !$actor->isHq()) {
            return false;
        }
        $central = in_array($permission, config('branch.central_permissions'), true);
        if ($central && !self::isCentral($actor)) {
            return false;
        }
        $module = self::moduleForPermission($permission);
        return $module === null || self::isCentral($actor) || self::moduleEnabled((int) $actor->department_id, $module);
    }

    public static function assertBranch(User $actor, $branchId, bool $active = false): void
    {
        abort_unless(filter_var($branchId, FILTER_VALIDATE_INT) !== false && (int) $branchId > 0, 422, 'Invalid branch.');
        abort_unless(self::canCrossBranches($actor) || (int) $actor->department_id === (int) $branchId, 403, 'Branch access denied.');
        $branch = DB::table('departments')->where('id', $branchId)->first();
        abort_unless($branch, 422, 'Branch does not exist.');
        if ($active) {
            abort_unless((int) $branch->status === 1, 422, 'This branch is inactive.');
        }
    }

    public static function visibleRoles(User $actor)
    {
        $query = Role::query()->where('guard_name', 'web');
        if (!self::isOwner($actor)) {
            $query->whereNotIn('name', ['SystemAdmin', 'Admin']);
        }
        if (!self::canCrossBranches($actor)) {
            $query->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $actor->department_id));
        }
        return $query;
    }

    public static function assertAssignableRole(User $actor, Role $role, int $branchId): void
    {
        abort_unless($role->guard_name === 'web', 422, 'Invalid role guard.');
        if (in_array($role->name, ['SystemAdmin', 'Admin'], true)) {
            abort_unless(self::isOwner($actor) && $branchId === Department::HQ_ID, 403, 'Only SystemAdmin can assign central administrators.');
        }
        abort_unless($role->branch_id === null || (int) $role->branch_id === $branchId, 403, 'Role belongs to another branch.');
        if (!self::isOwner($actor)) {
            foreach ($role->permissions as $permission) {
                $name = $permission->name;
                $central = in_array($name, array_merge(config('branch.central_permissions'), config('branch.owner_permissions')), true)
                    || self::isCentralAffiliatePermission($name);
                abort_if(in_array(strtolower($name), config('branch.owner_permissions'), true), 403, 'Only SystemAdmin can grant owner permissions.');
                abort_unless((!$central || self::isCentral($actor)) && $actor->getAllPermissions()->contains('name', $name), 403, 'Cannot grant permissions beyond your authority.');
            }
        }
        // A shared template can contain permissions for modules a branch has not enabled.
        // Effective permissions are intersected with the branch modules at request time.
    }

    public static function assertManageUser(User $actor, User $target): void
    {
        abort_if(!self::isOwner($actor) && $target->hasRole('SystemAdmin', 'web'), 404);
        self::assertBranch($actor, $target->department_id);
        abort_if(!self::isOwner($actor) && $target->hasRole('Admin', 'web'), 403, 'Only SystemAdmin can manage Admin accounts.');
    }

    public static function assertOwnerRemains(array $excludedUserIds): void
    {
        // Lock one stable row before counting to serialize concurrent demotions/deletions.
        $role = DB::table('roles')->where('name', 'SystemAdmin')->where('guard_name', 'web')->lockForUpdate()->first();
        if (!$role) {
            return;
        }
        $remaining = DB::table('users')->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->where('departments.id', Department::HQ_ID)->where('departments.status', 1)
            ->where('model_has_roles.model_type', (new User)->getMorphClass())
            ->where('model_has_roles.role_id', $role->id)
            ->where(fn ($q) => $q->whereNull('users.disable')->orWhere('users.disable', 0))
            ->whereNotIn('users.id', $excludedUserIds)->exists();
        abort_unless($remaining, 422, 'At least one active SystemAdmin must remain.');
    }
}
