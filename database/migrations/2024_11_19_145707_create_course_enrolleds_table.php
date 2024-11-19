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
        Schema::create('course_enrolleds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tracking')->default(1)->nullable();
            $table->integer('user_id');
            $table->integer('course_id');
            $table->float('purchase_price');
            $table->string('coupon')->nullable();
            $table->float('discount_amount')->default(0);
            $table->boolean('status')->default(1);
            $table->float('reveune')->default(0.00);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrolleds');
    }
};
