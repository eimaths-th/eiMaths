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
        Schema::create('courses_pennding', function (Blueprint $table) {
            $table->string('ref')->nullable(); // Message content from the user
            $table->string('name')->nullable(); // Name of the user
            $table->string('email')->nullable(); // Email of the user
            $table->string('telp')->nullable(); // Email of the user
            $table->string('course_name')->nullable(); // Email of the user
            $table->unsignedBigInteger('course_id'); // The course they are interested in
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses_pennding');
    }
};
