<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_MAP = [
        'course-pending-list' => 'affiliate-course-application-list',
        'course-pending-create' => 'affiliate-course-application-create',
        'course-pending-edit' => 'affiliate-course-application-edit',
        'course-pending-delete' => 'affiliate-course-application-delete',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            foreach (self::PERMISSION_MAP as $old_name => $new_name) {
                $new_permission = Permission::firstOrCreate([
                    'name' => $new_name,
                    'guard_name' => 'web',
                ]);

                $old_permission = Permission::query()
                    ->where('name', $old_name)
                    ->where('guard_name', 'web')
                    ->first();
                if ($old_permission === null) {
                    continue;
                }

                $this->MoveAssignments($old_permission->id, $new_permission->id);
                $old_permission->delete();
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            foreach (self::PERMISSION_MAP as $old_name => $new_name) {
                $old_permission = Permission::firstOrCreate([
                    'name' => $old_name,
                    'guard_name' => 'web',
                ]);
                $new_permission = Permission::query()
                    ->where('name', $new_name)
                    ->where('guard_name', 'web')
                    ->first();
                if ($new_permission === null) {
                    continue;
                }

                $this->MoveAssignments($new_permission->id, $old_permission->id);
                $new_permission->delete();
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function MoveAssignments(int $source_permission_id, int $target_permission_id): void
    {
        $permission_column = PermissionRegistrar::$pivotPermission;
        foreach ([
            config('permission.table_names.role_has_permissions'),
            config('permission.table_names.model_has_permissions'),
        ] as $table) {
            foreach (DB::table($table)->where($permission_column, $source_permission_id)->get() as $assignment) {
                $row = (array) $assignment;
                $row[$permission_column] = $target_permission_id;
                DB::table($table)->insertOrIgnore($row);
            }
            DB::table($table)->where($permission_column, $source_permission_id)->delete();
        }
    }
};
