<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    private const PERMISSIONS = [
        'credit-note-list',
        'credit-note-show',
        'credit-note-create',
        'credit-note-edit',
        'credit-note-issue',
        'credit-note-cancel',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        foreach (self::PERMISSIONS as $permission_name) {
            Permission::findOrCreate($permission_name, 'web');
        }
        app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::query()->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->delete();
        app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
