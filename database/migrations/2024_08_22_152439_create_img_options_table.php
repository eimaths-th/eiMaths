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
        Schema::create('img_options', function (Blueprint $table) {
            $table->id();
            $table->string('img_name');
            $table->boolean('correct')->default(0);
            $table->foreignId('question_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('img_options');
    }
};
