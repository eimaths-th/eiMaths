<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_shipments', function (Blueprint $table): void {
            $table->id();
            $table->string('shipment_number', 50)->unique();
            $table->foreignId('destination_centre_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();
            $table->string('destination_centre_code')->nullable();
            $table->string('destination_centre_name')->nullable();
            $table->string('status', 40)->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('created_by_name')->nullable();
            $table->foreignId('dispatched_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('dispatched_by_name')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->foreignId('centre_received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('centre_received_by_name')->nullable();
            $table->timestamp('centre_received_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('cancelled_by_name')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamp('dispatch_notified_at')->nullable();
            $table->timestamp('centre_received_notified_at')->nullable();
            $table->timestamp('student_completion_notified_at')->nullable();

            $table->timestamps();
            $table->index(
                ['destination_centre_id', 'status', 'created_at'],
                'shipments_destination_status_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_shipments');
    }
};
