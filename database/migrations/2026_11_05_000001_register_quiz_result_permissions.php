<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\{Permission, Role};
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quiz-results-list', 'quiz-results-view'] as $permission_name) {
            Permission::findOrCreate($permission_name, 'web');
        }
        Role::where('guard_name', 'web')->with('permissions')->chunkById(100, function ($roles): void {
            foreach ($roles as $role) {
                if (!$role->permissions->contains('name', 'quiz-manage')) {
                    continue;
                }
                $role->givePermissionTo('quiz-results-list');
                if ($role->permissions->contains('name', 'quiz-list')) {
                    $role->givePermissionTo('quiz-results-view');
                }
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep identities and grants that may have been independently assigned after rollout.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
