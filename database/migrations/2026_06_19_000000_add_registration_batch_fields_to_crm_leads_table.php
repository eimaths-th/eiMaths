<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_leads', 'registration_batch_id')) {
                $table->string('registration_batch_id')->nullable()->after('duplicate_checked_at');
                $table->index('registration_batch_id');
            }

            if (! Schema::hasColumn('crm_leads', 'superseded_at')) {
                $table->timestamp('superseded_at')->nullable()->after('registration_batch_id');
            }

            if (! Schema::hasColumn('crm_leads', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('superseded_at');
            }

            if (! Schema::hasColumn('crm_leads', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('cancelled_at');
            }

            if (! Schema::hasColumn('crm_leads', 'superseded_by_batch_id')) {
                $table->string('superseded_by_batch_id')->nullable()->after('cancel_reason');
            }

            if (! Schema::hasColumn('crm_leads', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('superseded_by_batch_id');
                $table->index('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            if (Schema::hasColumn('crm_leads', 'registration_batch_id')) {
                $table->dropIndex(['registration_batch_id']);
            }

            if (Schema::hasColumn('crm_leads', 'is_active')) {
                $table->dropIndex(['is_active']);
            }

            $table->dropColumn([
                'registration_batch_id',
                'superseded_at',
                'cancelled_at',
                'cancel_reason',
                'superseded_by_batch_id',
                'is_active',
            ]);
        });
    }
};
