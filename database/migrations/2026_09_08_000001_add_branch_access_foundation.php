<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->json('enabled_modules')->nullable();
            $table->string('branch_type', 24)->default('branch')->index();
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('display_name')->nullable();
        });
        foreach (['crm_leads', 'tracking_links', 'file_uploads'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->index();
            });
        }
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
        });
        Schema::create('branch_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('action', 64);
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->index();
        });
        Schema::create('branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->index();
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->unsignedBigInteger('actor_id');
            $table->text('reason');
            $table->timestamp('created_at');
        });
        \Illuminate\Support\Facades\DB::table('departments')->where('id', 1)->update(['branch_type' => 'hq']);
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_transfers');
        Schema::dropIfExists('branch_audit_logs');
        Schema::table('file_uploads', fn (Blueprint $table) => $table->dropColumn(['branch_id', 'uploaded_by']));
        Schema::table('crm_leads', fn (Blueprint $table) => $table->dropColumn('branch_id'));
        Schema::table('tracking_links', fn (Blueprint $table) => $table->dropColumn('branch_id'));
        Schema::table('roles', fn (Blueprint $table) => $table->dropColumn(['branch_id', 'display_name']));
        Schema::table('departments', fn (Blueprint $table) => $table->dropColumn(['enabled_modules', 'branch_type']));
    }
};
