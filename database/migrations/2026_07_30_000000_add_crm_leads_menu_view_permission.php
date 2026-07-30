<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_NAME = 'crm-leads-menu-view';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => self::PERMISSION_NAME,
            'guard_name' => 'web',
        ]);

        // Preserve the current menu visibility for existing roles after deployment.
        // Administrators can then remove this permission from selected roles.
        if ($permission->wasRecentlyCreated) {
            Role::where('guard_name', 'web')
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::where([
            'name' => self::PERMISSION_NAME,
            'guard_name' => 'web',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
