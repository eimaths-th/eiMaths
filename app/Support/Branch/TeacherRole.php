<?php

namespace App\Support\Branch;

use Spatie\Permission\Models\Role;

final class TeacherRole
{
    public static function scopedName(int $branchId): string
    {
        return 'branch-'.$branchId.'-teacher';
    }

    public static function isTeacherRole(Role $role): bool
    {
        if ($role->guard_name !== 'web') {
            return false;
        }

        if ($role->name === 'Teacher') {
            return $role->branch_id === null;
        }

        return (int) $role->branch_id > 0
            && $role->name === self::scopedName((int) $role->branch_id);
    }

    /** @return list<string> */
    public static function teacherRoleNames(): array
    {
        // Display names are editable and never establish a role's identity.
        return Role::query()->where('guard_name', 'web')
            ->where(fn ($query) => $query->where('name', 'Teacher')->orWhereNotNull('branch_id'))
            ->get(['id', 'name', 'guard_name', 'branch_id'])
            ->filter(fn (Role $role): bool => self::isTeacherRole($role))
            ->pluck('name')->values()->all();
    }
}
