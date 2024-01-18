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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('centre')->default(0);
            $table->integer('cid')->nullable();
            $table->string('order_number')->nullable();
            $table->text('detail')->nullable();
            $table->integer('status')->default(0);
            $table->decimal('refund', 12, 2)->nullable();
            $table->decimal('register_fee', 12, 2)->nullable();
            $table->decimal('access_fee', 12, 2)->nullable();
            $table->decimal('orther_fee', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('discount_book', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->integer('payment')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
