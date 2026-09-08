<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        $permissions = [
            'branch-modules-manage', 'branch-transfer', 'central-settings-manage',
            'financial-settings-manage', 'integration-settings-manage', 'support-manage',
            'student-export', 'receipt-export', 'invoice-export', 'order-export',
            'parent-list', 'parent-create', 'parent-edit', 'parent-delete',
            'history-list', 'history-create', 'history-edit', 'history-delete',
            'teaching-list', 'teaching-create', 'teaching-edit', 'teaching-delete',
            'course-pending-list', 'course-pending-create', 'course-pending-edit', 'course-pending-delete',
            'crm-leads-create', 'crm-leads-edit', 'crm-leads-delete', 'quiz-list',
        ];
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        // Shared templates remain stable names; no branch-specific Admin clones.
        foreach (['SystemAdmin', 'Admin', 'BranchAdmin', 'Teacher'] as $name) {
            Role::findOrCreate($name, 'web');
        }
        // Derive only equivalent legacy capabilities; do not grant all new capabilities.
        $equivalents = [
            'parent-list' => 'student-list', 'parent-create' => 'student-create',
            'parent-edit' => 'student-edit', 'parent-delete' => 'student-delete',
            'history-list' => 'student-list', 'history-create' => 'student-edit',
            'history-edit' => 'student-edit', 'history-delete' => 'student-delete',
            'quiz-list' => 'quiz-manage',
        ];
        foreach (Role::where('guard_name', 'web')->get() as $role) {
            foreach ($equivalents as $new => $old) {
                if ($role->permissions->contains('name', $old)) {
                    $role->givePermissionTo($new);
                }
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep identities and permission assignments on rollback to avoid removing
        // permissions created independently after rollout. Schema rollback is separate.
    }
};
