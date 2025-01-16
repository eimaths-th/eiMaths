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
        Schema::create('affiliate_user_banking', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->unsignedBigInteger('user_id'); // Foreign key to reference users
            $table->string('bank_name'); // Name of the bank
            $table->string('account_number'); // Bank account number
            $table->string('account_name'); // Account holder's name
            // $table->string('branch_name')->nullable(); // Optional branch name
            // $table->string('swift_code')->nullable(); // Optional SWIFT code for international transfers
            // $table->string('iban')->nullable(); // Optional IBAN for international transfers

            // $table->boolean('is_default')->default(false); // Whether this is the user's default bank account

            $table->timestamps(); // Created at and updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_user_banking');
    }
};
