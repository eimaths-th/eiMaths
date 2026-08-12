<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_running_numbers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('centre_id')->constrained('departments')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedBigInteger('number')->default(0);
            $table->timestamps();
            $table->unique(['centre_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_running_numbers');
    }
};
