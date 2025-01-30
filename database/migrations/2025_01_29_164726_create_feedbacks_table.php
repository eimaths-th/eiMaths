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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['Bug', 'Suggestion', 'Other'])->default('Bug');
            $table->enum('status', ['New', 'In Progress', 'Resolved'])->default('New');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->string('attachment_path')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('admin_comments')->nullable();
            $table->unsignedBigInteger('admin_resolved')->nullable(); //user_id for admin

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
