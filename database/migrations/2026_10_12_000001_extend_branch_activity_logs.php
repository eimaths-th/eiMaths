<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branch_audit_logs', function (Blueprint $table) {
            // NULL category identifies legacy rows whose target branch is not reliable.
            $table->string('category', 24)->nullable();
            $table->string('module', 24)->nullable();
            $table->string('outcome', 24)->nullable();
            $table->unsignedBigInteger('actor_branch_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->boolean('actor_is_owner')->default(false);
            $table->boolean('subject_is_owner')->default(false);
            $table->unsignedBigInteger('related_branch_id')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->index(['branch_id', 'category', 'created_at'], 'branch_activity_scope_time');
            $table->index(['related_branch_id', 'created_at'], 'branch_activity_related_time');
            $table->index(['category', 'created_at'], 'branch_activity_category_time');
        });
        $permission = Permission::findOrCreate('activity-log-list', 'web');
        // Only standard administrators receive it automatically; custom roles are opt-in.
        foreach (Role::where('guard_name', 'web')->whereIn('name', ['SystemAdmin', 'Admin', 'BranchAdmin'])->get() as $role) {
            $role->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('branch_audit_logs', function (Blueprint $table) {
            $table->dropIndex('branch_activity_scope_time');
            $table->dropIndex('branch_activity_related_time');
            $table->dropIndex('branch_activity_category_time');
            $table->dropColumn(['category', 'module', 'outcome', 'actor_branch_id', 'actor_name', 'actor_is_owner', 'subject_is_owner', 'related_branch_id', 'before_values', 'after_values', 'metadata', 'ip_address', 'user_agent']);
        });
    }
};
