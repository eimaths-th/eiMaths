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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level_type')->default(0);
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('half_price', 12, 2)->nullable();
            $table->decimal('book_price', 12, 2)->nullable();
            $table->decimal('book_half_price', 12, 2)->nullable();
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('full_name_th')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
