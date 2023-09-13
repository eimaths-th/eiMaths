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
        Schema::create('invoice_running_numbers', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('month');
            $table->string('centre')->nullable();
            $table->unsignedInteger('number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_running_numbers');
    }
};
