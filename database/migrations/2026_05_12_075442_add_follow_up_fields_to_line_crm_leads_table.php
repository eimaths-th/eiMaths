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
            $table->timestamp('last_follow_up_at')->nullable();
            $table->integer('follow_up_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('line_crm_leads', function (Blueprint $table) {
            $table->dropColumn(['last_follow_up_at', 'follow_up_count']);
        });
    }
};
