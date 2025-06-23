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
        Schema::create('questions_fraction_answers', function (Blueprint $table) {
            $table->id();
            // store the question_id to know which question this answer belongs to
            $table->foreignId('question_id')->constrained('test_sys_questions')->onDelete('cascade');
            $table->integer('numerator');
            $table->integer('denominator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions_fraction_answers');
    }
};
