<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            $manage = Permission::findOrCreate('ebook-manage', 'web');
            $activity = Permission::findOrCreate('ebook-activity-list', 'web');

            // Preserve equivalent legacy access once. Future grants/revocations
            // use E-book permissions independently of course/student permissions.
            foreach (['course-manage' => [$manage->id, $activity->id], 'student-list' => [$activity->id]] as $old => $newIds) {
                $oldId = Permission::where('name', $old)->where('guard_name', 'web')->value('id');
                if (!$oldId) { continue; }

                foreach (['role_has_permissions', 'model_has_permissions'] as $table) {
                    foreach (DB::table($table)->where('permission_id', $oldId)->get() as $assignment) {
                        foreach ($newIds as $id) {
                            DB::table($table)->insertOrIgnore(array_replace((array) $assignment, ['permission_id' => $id]));
                        }
                    }
                }
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permission assignments, including changes made by administrators.
    }
};
