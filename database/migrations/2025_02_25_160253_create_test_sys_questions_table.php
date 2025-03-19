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
        Schema::create('test_sys_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text'); // Store the question as text
            $table->integer('answer_numerator'); // Numerator of the correct answer
            $table->integer('answer_denominator'); // Denominator of the correct answer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sys_questions');
    }
};
