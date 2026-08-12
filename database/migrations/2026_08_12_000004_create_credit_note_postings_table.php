<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->restrictOnDelete();
            $table->foreignId('centre_id')->constrained('departments')->restrictOnDelete();
            $table->string('posting_type', 20);
            $table->date('effective_date');
            $table->bigInteger('taxable_base_delta_minor');
            $table->bigInteger('vat_delta_minor');
            $table->bigInteger('exempt_delta_minor');
            $table->bigInteger('out_of_scope_delta_minor');
            $table->bigInteger('grand_total_delta_minor');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('correlation_id', 96)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['credit_note_id', 'posting_type']);
            $table->index(['centre_id', 'effective_date']);
            $table->index(['posting_type', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_postings');
    }
};
