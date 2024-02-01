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
        Schema::create('history_log', function (Blueprint $table) {
            $table->id();
            $table->integer('centre')->nullable();
            $table->integer('student_id')->nullable();

            $table->integer('level_id_old')->nullable();
            $table->string('level_name_old')->nullable();
            $table->string('term_old')->nullable();
            $table->string('bookuse_old')->nullable();
            $table->string('course_remaining_old')->nullable();
            $table->string('date_old')->nullable();
            $table->string('stime_old')->nullable();
            $table->string('etime_old')->nullable();
            $table->string('comment_old')->nullable();

            $table->integer('level_id_new')->nullable();
            $table->string('level_name_new')->nullable();
            $table->string('term_new')->nullable();
            $table->string('bookuse_new')->nullable();
            $table->string('course_remaining_new')->nullable();
            $table->string('date_new')->nullable();
            $table->string('stime_new')->nullable();
            $table->string('etime_new')->nullable();
            $table->string('comment_new')->nullable();

            $table->integer('approve_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_log');
    }
};
