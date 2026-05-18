<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('line_crm_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('line_crm_leads', 'last_follow_up_at')) {
                $table->timestamp('last_follow_up_at')->nullable();
            }

            if (!Schema::hasColumn('line_crm_leads', 'follow_up_count')) {
                $table->integer('follow_up_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('line_crm_leads', function (Blueprint $table) {
            if (Schema::hasColumn('line_crm_leads', 'last_follow_up_at')) {
                $table->dropColumn('last_follow_up_at');
            }

            if (Schema::hasColumn('line_crm_leads', 'follow_up_count')) {
                $table->dropColumn('follow_up_count');
            }
        });
    }
};
