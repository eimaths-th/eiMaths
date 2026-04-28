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
        Schema::create('guest_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_test_id')->constrained('guest_tests')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->unsignedBigInteger('option_id')->nullable();    // คำตอบแบบตัวเลือก
            $table->unsignedBigInteger('image_id')->nullable();     // คำตอบแบบรูปภาพ
            $table->string('written_answer')->nullable();           // คำตอบแบบเขียน
            $table->string('fraction')->nullable();                 // คำตอบแบบเศษส่วน
            $table->boolean('correct')->default(false);             // ถูก/ผิด
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_answers');
    }
};
