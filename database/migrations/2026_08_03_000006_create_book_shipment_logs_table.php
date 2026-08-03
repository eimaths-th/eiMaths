<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_shipment_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_shipment_id')
                ->constrained('book_shipments')
                ->restrictOnDelete();
            $table->foreignId('acted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action', 40);
            $table->string('actor_name')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['book_shipment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_shipment_logs');
    }
};
