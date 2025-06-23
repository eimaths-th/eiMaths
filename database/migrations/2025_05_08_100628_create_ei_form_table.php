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
        Schema::create('ei_form', function (Blueprint $table) {
            $table->id();
            $table->string('first_name_parent')->nullable();
            $table->string('last_name_parent')->nullable();
            $table->string('telp_parent')->nullable();
            $table->string('email_parent')->nullable();
            $table->string('first_name_student')->nullable();
            $table->string('last_name_student')->nullable();
            $table->string('nick_name_student')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ei_form');
    }
};
