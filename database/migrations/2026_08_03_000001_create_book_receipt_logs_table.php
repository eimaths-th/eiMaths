<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_receipt_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_detail_list_id')
                ->nullable()
                ->constrained('order_detail_lists')
                ->nullOnDelete();
            $table->foreignId('student_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('acted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action', 20);
            $table->string('order_number')->nullable();
            $table->string('book_name');
            $table->string('student_code')->nullable();
            $table->string('student_name');
            $table->string('actor_name');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_receipt_logs');
    }
};
