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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('ctype')->default(0);
            $table->integer('order')->default(0);
            $table->integer('centre')->default(0);
            $table->string('code')->nullable();
            $table->string('start_date')->nullable();
            $table->integer('start_term')->nullable();
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('school')->nullable();
            $table->integer('gender')->default(1);
            $table->string('birth_date')->nullable();
            $table->longText('address')->nullable();
            $table->string('postcode')->nullable();
            $table->string('telephone')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_email')->nullable();
            $table->string('father_mobile')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_email')->nullable();
            $table->string('mother_mobile')->nullable();
            $table->integer('level')->default(0);
            $table->integer('term')->default(0);
            $table->integer('bookuse')->default(0);
            $table->string('level_name')->nullable();
            $table->string('term_name')->nullable();
            $table->string('bookuse_name')->nullable();
            $table->integer('level2')->default(0);
            $table->integer('term2')->default(0);
            $table->integer('bookuse2')->default(0);
            $table->string('level2_name')->nullable();
            $table->string('term2_name')->nullable();
            $table->string('bookuse2_name')->nullable();
            $table->integer('discontinued')->default(0);
            $table->string('discontinued_date')->nullable();
            $table->string('discontinued_reason')->nullable();
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
        Schema::dropIfExists('contacts');
    }
};
