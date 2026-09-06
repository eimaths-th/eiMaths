<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_tutorial_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')->unique()->constrained('affiliate_profiles')->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status', 20);
            $table->string('step_key', 50);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_tutorial_progress');
    }
};
