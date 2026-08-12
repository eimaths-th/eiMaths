<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->string('line_type', 40);
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->string('tax_treatment', 40);
            $table->unsignedInteger('vat_rate_basis_points')->default(0);
            $table->decimal('original_quantity', 12, 2);
            $table->unsignedBigInteger('original_unit_price_minor');
            $table->unsignedBigInteger('original_discount_minor');
            $table->unsignedBigInteger('original_line_total_minor');
            $table->decimal('credit_quantity', 12, 2)->nullable();
            $table->unsignedBigInteger('credit_amount_minor');
            $table->unsignedBigInteger('credit_taxable_base_minor');
            $table->unsignedBigInteger('credit_vat_minor');
            $table->unsignedBigInteger('credit_exempt_minor');
            $table->unsignedBigInteger('credit_out_of_scope_minor');
            $table->timestamps();

            $table->unique(['credit_note_id', 'source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
    }
};
