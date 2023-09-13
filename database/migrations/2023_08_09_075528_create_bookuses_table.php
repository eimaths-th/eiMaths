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
        Schema::create('bookuses', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->integer('level_id')->default(0);
            $table->integer('term_id')->default(0);
            $table->string('name')->nullable();
            $table->integer('qty')->default(0);
            $table->string('unit')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookuses');
    }
};
