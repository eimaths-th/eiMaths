<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = [
        'affiliate-course-application-list',
        'affiliate-course-application-create',
        'affiliate-course-application-edit',
        'affiliate-course-application-delete',
    ];

    private const ROLES = ['SystemAdmin', 'Admin', 'BranchAdmin'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(self::PERMISSIONS)->map(
            fn (string $name): Permission => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ])
        );

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::ROLES)
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep assignments on rollback. They may have existed before this migration,
        // and the permission rename migration will preserve them when rolled back.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
