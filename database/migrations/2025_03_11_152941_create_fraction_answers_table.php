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
        Schema::create('fraction_answers', function (Blueprint $table) {
            $table->id();
            $table->integer('question_id');
            $table->string('type'); // 'written' or 'option'
            $table->integer('numerator');
            $table->integer('denominator');
            $table->boolean('correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraction_answers');
    }
};
