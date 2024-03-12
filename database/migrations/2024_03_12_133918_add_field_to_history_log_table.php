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
        Schema::table('history_log', function (Blueprint $table) {
            //
            $table->string('start_date_old')->nullable()->after('etime_old');
            $table->string('end_date_old')->nullable()->after('etime_old');
            $table->string('start_date_new')->nullable()->after('etime_new');
            $table->string('end_date_new')->nullable()->after('etime_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_log', function (Blueprint $table) {
            //
        });
    }
};
