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
        Schema::create('histrories', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable(0);//id ของนักเรียน
            $table->integer('teacher_id')->nullable(0);
            $table->integer('level_id')->nullable(0);//ระดับชั้น
            $table->string('term')->nullable(0);//เทอมของนักเรียน
            $table->string('bookuse')->nullable(0);//หนังสือที่ใช่้
            $table->string('course_remaining')->nullable(0);//จำนวนคอร์สคงเหลือ
            $table->date('date')->nullable(0);//วันที่เข้าเรียน
            $table->time('stime')->nullable(0);//เวลาเข้าเรียน
            $table->time('etime')->nullable(0);//เวลาเลิกเรียน
            $table->date('start_date')->nullable(0);//วันแรกที่เรียน
            $table->string('comment')->nullable(0);//คอมเมนต์
            $table->string('signature')->nullable(0);//ลานเซ็น
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histrories');
    }
};
