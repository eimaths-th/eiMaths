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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->integer('centre')->default(0);
            $table->integer('cid')->nullable();
            $table->integer('oid')->nullable();
            $table->string('student_no')->nullable();
            $table->string('student_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('level')->nullable();
            $table->decimal('total_fee', 12, 2)->nullable();
            $table->integer('start_term')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('receipts');
    }
};
