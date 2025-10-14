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
            // ตรวจสอบและเพิ่มคอลัมน์ pen_name หากยังไม่มี
            if (!Schema::hasColumn('blogs', 'pen_name')) {
                $table->string('pen_name', 255)->nullable()->after('meta_keywords');
            }

            // ตรวจสอบและเพิ่มคอลัมน์ user_id หากยังไม่มี
            if (!Schema::hasColumn('blogs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('pen_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // ลบคอลัมน์ user_id หากมีอยู่
            if (Schema::hasColumn('blogs', 'user_id')) {
                $table->dropColumn('user_id');
            }

            // ลบคอลัมน์ pen_name หากมีอยู่
            if (Schema::hasColumn('blogs', 'pen_name')) {
                $table->dropColumn('pen_name');
            }
        });
    }
};