<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        $permission = Permission::findOrCreate('course-list', 'web');

        // A teacher who could already view teaching data may read the public
        // course catalog when HQ enables the Courses module for that branch.
        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if ($role->hasPermissionTo('teaching-list')) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep existing assignments; removing the permission on rollback could
        // revoke access that an administrator assigned after deployment.
    }
};
