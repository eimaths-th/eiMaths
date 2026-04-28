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
        Schema::create('guest_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_quizzer_id')->constrained('guest_quizzers')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('result')->default(0);           // คะแนนที่ได้
            $table->integer('total_questions')->default(0);  // จำนวนข้อทั้งหมด
            $table->integer('time_spent')->default(0);       // เวลาที่ใช้ (วินาที)
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_tests');
    }
};
