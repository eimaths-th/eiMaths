<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->restrictOnDelete();
            $table->string('operation', 20);
            $table->string('status_before', 20)->nullable();
            $table->string('status_after', 20);
            $table->bigInteger('amount_minor');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name_snapshot')->nullable();
            $table->string('reason_code', 80)->nullable();
            $table->string('correlation_id', 96)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_events');
    }
};
