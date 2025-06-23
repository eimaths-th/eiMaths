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
        Schema::create('affiliate_config_commission', function (Blueprint $table) {
            $table->id();
            $table->integer('course_per_month')->nullable();
            $table->decimal('comission_per_course_10_percent')->nullable();
            $table->decimal('comission_per_course_15_percent')->nullable();
            // $table->integer('percentage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_config_commission');
    }
};
