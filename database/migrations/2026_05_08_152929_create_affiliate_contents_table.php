<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * สร้างตาราง affiliate_contents สำหรับเก็บสื่อการตลาดที่แยกออกจาก promotion_img
     */
    public function up(): void
    {
        Schema::create('affiliate_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');                  // ชื่อสื่อ
            $table->string('file_path');               // path ไฟล์ (รูป/วิดีโอ)
            $table->enum('type', ['image', 'video'])->default('image'); // ประเภทไฟล์
            $table->text('description')->nullable();   // คำอธิบาย/caption ตัวอย่าง
            $table->enum('status', ['enabled', 'disabled'])->default('enabled'); // สถานะ
            $table->unsignedBigInteger('created_by')->nullable(); // Admin ผู้สร้าง
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_contents');
    }
};
