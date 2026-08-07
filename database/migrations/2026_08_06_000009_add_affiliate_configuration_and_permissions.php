<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const SETTINGS = [
        'commission_rate_basis_points' => '2000',
        'commission_holding_days' => '10',
        'attribution_duration_days' => '180',
        'minimum_withdrawal_minor' => '1000',
        'withdrawal_approval_required' => '1',
    ];

    private const PERMISSIONS = [
        'affiliate-list',
        'affiliate-approve',
        'affiliate-suspend',
        'affiliate-config-edit',
        'affiliate-commission-hold',
        'affiliate-refund-record',
        'affiliate-withdrawal-approve',
        'affiliate-withdrawal-pay',
        'affiliate-withdrawal-list',
        'affiliate-reconciliation',
    ];

    public function up(): void
    {
        if (DB::getSchemaBuilder()->hasTable('affiliate_configurations')) {
            foreach (self::SETTINGS as $key => $value) {
                if (DB::table('affiliate_configurations')->where('key', $key)->exists()) {
                    continue;
                }

                DB::table('affiliate_configurations')->insert([
                    'key' => $key,
                    'value' => $value,
                    'lms_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(self::PERMISSIONS)->map(
            fn (string $name): Permission => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
        );

        foreach (['SystemAdmin', 'Admin'] as $role_name) {
            $role = Role::where('name', $role_name)->where('guard_name', 'web')->first();
            if ($role !== null) {
                foreach ($permissions as $permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('guard_name', 'web')->whereIn('name', self::PERMISSIONS)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
