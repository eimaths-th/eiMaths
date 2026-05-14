<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_MAP = [
        'blog-list' => 'faq-list',
        'blog-create' => 'faq-create',
        'blog-edit' => 'faq-edit',
        'blog-delete' => 'faq-delete',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_values(self::PERMISSION_MAP) as $faq_permission) {
            Permission::firstOrCreate(['name' => $faq_permission]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_values(self::PERMISSION_MAP) as $faq_permission) {
            $permission = Permission::where('name', $faq_permission)->first();

            if ($permission) {
                $permission->delete();
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
