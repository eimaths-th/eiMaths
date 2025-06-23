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
        Schema::create('qr_payment', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_numbers')->nullable();
            $table->string('promptpay_numbers')->nullable();
            $table->string('img')->nullable(); //default image path /img/qrcode/qr.png

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_payment');
    }
};
