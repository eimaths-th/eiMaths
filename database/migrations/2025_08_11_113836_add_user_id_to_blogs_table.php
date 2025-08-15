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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('pen_name')->nullable()->after('meta_keywords');
            $table->foreignid('user_id')->nullable()->constrained()->ondelete('cascade')->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropforeign(['user_id']);
            $table->dropcolumn('user_id');
            $table->dropcolumn('pen_name');
        });
    }
};
