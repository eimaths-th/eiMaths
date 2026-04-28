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
        Schema::create('guest_quizzers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');              // ชื่อ
            $table->string('last_name');               // นามสกุล
            $table->string('nickname')->nullable();    // ชื่อเล่น
            $table->string('grade');                   // ชั้นเรียน (K1, K2, P1-P6, M1-M3)
            $table->string('school')->nullable();      // โรงเรียน
            $table->string('email')->unique();         // ใช้ login
            $table->string('phone');                   // เบอร์โทร (ใช้เป็น password)
            $table->string('password');                // hashed phone
            $table->timestamp('expires_at');           // created_at + 7 days
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_quizzers');
    }
};
