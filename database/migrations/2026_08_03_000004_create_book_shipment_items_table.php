<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_shipment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_shipment_id')
                ->constrained('book_shipments')
                ->restrictOnDelete();
            $table->foreignId('source_order_detail_list_id')
                ->nullable()
                ->constrained('order_detail_lists')
                ->nullOnDelete();
            $table->foreignId('source_student_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('source_order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('replaces_shipment_item_id')
                ->nullable()
                ->constrained('book_shipment_items')
                ->nullOnDelete();

            $table->string('source_student_code')->nullable();
            $table->string('source_student_name')->nullable();
            $table->string('source_order_number')->nullable();
            $table->string('book_name');
            $table->string('level')->nullable();
            $table->string('term')->nullable();
            $table->string('centre_status', 30)->index();
            $table->text('centre_note')->nullable();
            $table->timestamps();

            $table->unique(
                ['book_shipment_id', 'source_order_detail_list_id'],
                'shipment_source_book_unique'
            );
            $table->index(
                ['source_order_detail_list_id', 'centre_status'],
                'shipment_source_status_idx'
            );
            $table->index(
                ['replaces_shipment_item_id', 'centre_status'],
                'shipment_replacement_status_idx'
            );
            $table->index(
                ['book_shipment_id', 'centre_status'],
                'shipment_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_shipment_items');
    }
};
