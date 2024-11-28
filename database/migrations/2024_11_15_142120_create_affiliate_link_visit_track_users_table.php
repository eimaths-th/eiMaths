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
        Schema::create('affiliate_link_visit_track_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_link_id');
            $table->string('ip');
            $table->string('agent');
            $table->date('date');
            $table->foreign('affiliate_link_id')->on('affiliate_links')->references('id')->onDelete('cascade');
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_link_visit_track_users');
    }
};
