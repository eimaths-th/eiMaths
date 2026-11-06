<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_NAME = 'student-activity-list';
    private const GRANTED_ROLE_NAMES = ['Admin', 'BranchAdmin'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::findOrCreate(self::PERMISSION_NAME, 'web');
        Role::query()->where('guard_name', 'web')->whereIn('name', self::GRANTED_ROLE_NAMES)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('guard_name', 'web')->where('name', self::PERMISSION_NAME)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
