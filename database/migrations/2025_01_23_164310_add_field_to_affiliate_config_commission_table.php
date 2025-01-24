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
        Schema::table('affiliate_config_commission', function (Blueprint $table) {
            //
            $table->integer('user_per_course_low')->nullable()->after('comission_per_course_15_percent');
            $table->integer('user_per_course_high')->nullable()->after('user_per_course_low');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_config_commission', function (Blueprint $table) {
            //
        });
    }
};
