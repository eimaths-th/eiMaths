<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLE_NAME = 'Affiliate-user';

    private const RESTRICTED_PERMISSIONS = [
        'blog-list',
        'blog-create',
        'blog-edit',
        'blog-delete',
        'crm-leads-menu-view',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where([
            'name' => self::ROLE_NAME,
            'guard_name' => 'web',
        ])->first();

        if ($role !== null) {
            $permissions = Permission::where('guard_name', 'web')
                ->whereIn('name', self::RESTRICTED_PERMISSIONS)
                ->get();

            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where([
            'name' => self::ROLE_NAME,
            'guard_name' => 'web',
        ])->first();

        if ($role !== null) {
            $permissions = Permission::where('guard_name', 'web')
                ->whereIn('name', self::RESTRICTED_PERMISSIONS)
                ->get();

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
