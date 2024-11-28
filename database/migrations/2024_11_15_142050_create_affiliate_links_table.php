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
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->string('affiliate_link', 500);
            $table->unsignedBigInteger('owner_id');
            $table->integer('visits')->default(0);
            $table->integer('registered')->default(0);
            $table->integer('purchased')->default(0);
            $table->integer('commissions')->default(0);
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_links');
    }
};
