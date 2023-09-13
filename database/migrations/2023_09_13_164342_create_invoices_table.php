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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('centre')->default(0);
            $table->integer('cid')->nullable();
            $table->string('student_no')->nullable();
            $table->string('student_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_date')->nullable();
            $table->integer('oid')->nullable();
            $table->string('level')->nullable();
            $table->decimal('total_fee', 12, 2)->nullable();
            $table->integer('order_term')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('invoices');
    }
};
