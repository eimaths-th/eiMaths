<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_NAMES = [
        'book-shipment-list',
        'book-shipment-create',
        'book-shipment-dispatch',
        'book-shipment-cancel',
        'book-shipment-receive',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSION_NAMES as $permission_name) {
            Permission::firstOrCreate([
                'name' => $permission_name,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::PERMISSION_NAMES)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
