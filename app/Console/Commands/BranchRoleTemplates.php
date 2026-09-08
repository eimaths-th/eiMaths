<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BranchRoleTemplates extends Command
{
    protected $signature = 'branch:roles {--role=* : Teacher, BranchAdmin, or Admin; omit to preview all} {--apply : Apply the reviewed role defaults}';
    protected $description = 'Preview or explicitly apply standard branch roles without creating roles per branch';

    public function handle(): int
    {
        $templates = config('branch-role-templates');
        $names = $this->option('role') ?: array_keys($templates);
        if (array_diff($names, array_keys($templates))) {
            $this->error('Choose Teacher, BranchAdmin, or Admin.');
            return self::INVALID;
        }
        foreach ($names as $name) {
            $role = Role::where('name', $name)->where('guard_name', 'web')->first();
            $current = $role?->permissions->pluck('name')->all() ?? [];
            $wanted = array_values(array_unique($templates[$name]));
            $this->info($name.($this->option('apply') ? ' — applying' : ' — preview only'));
            $this->table(['Change', 'Permission'], array_merge(
                array_map(fn ($p) => ['ADD', $p], array_values(array_diff($wanted, $current))),
                array_map(fn ($p) => ['REMOVE', $p], array_values(array_diff($current, $wanted)))
            ));
            if ($this->option('apply')) {
                DB::transaction(function () use ($name, $wanted) {
                    foreach ($wanted as $permission) { Permission::findOrCreate($permission, 'web'); }
                    $role = Role::findOrCreate($name, 'web');
                    abort_unless($role->branch_id === null, 422, 'Standard role must be global.');
                    $role->syncPermissions($wanted);
                    DB::table('branch_audit_logs')->insert([
                        'action' => 'role-template-applied', 'subject_type' => Role::class, 'subject_id' => $role->id,
                        'changes' => json_encode(['source' => 'console', 'role' => $name, 'permissions' => $wanted]), 'created_at' => now(),
                    ]);
                });
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return self::SUCCESS;
    }
}
