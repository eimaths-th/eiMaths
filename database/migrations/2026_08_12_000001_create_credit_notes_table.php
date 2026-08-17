<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_id')->constrained('receipts')->restrictOnDelete();
            $table->foreignId('centre_id')->constrained('departments')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->string('number')->nullable()->unique();
            $table->string('currency', 3)->default('THB');
            $table->string('reason_code', 80)->nullable();
            $table->text('reason_details')->nullable();
            $table->date('adjustment_event_date')->nullable();
            $table->text('late_issue_reason')->nullable();
            $table->date('issue_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('issue_idempotency_key', 120)->nullable()->unique();
            $table->date('cancellation_effective_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('cancel_idempotency_key', 120)->nullable()->unique();
            $table->string('source_snapshot_hash', 64)->nullable();
            $table->timestamp('source_receipt_updated_at')->nullable();
            $table->string('original_receipt_number')->nullable();
            $table->date('original_receipt_date')->nullable();
            $table->text('original_billing_details_text')->nullable();
            $table->string('buyer_name')->nullable();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_tax_id', 13)->nullable();
            $table->boolean('buyer_is_vat_registered')->nullable();
            $table->string('buyer_branch_type', 30)->nullable();
            $table->string('buyer_branch_code', 10)->nullable();
            $table->string('issuer_name')->nullable();
            $table->text('issuer_address')->nullable();
            $table->string('issuer_tax_id', 13)->nullable();
            $table->string('issuer_branch_type', 30)->nullable();
            $table->string('issuer_branch_code', 10)->nullable();
            $table->unsignedBigInteger('original_taxable_base_minor')->nullable();
            $table->unsignedBigInteger('original_vat_minor')->nullable();
            $table->unsignedBigInteger('original_exempt_minor')->nullable();
            $table->unsignedBigInteger('original_out_of_scope_minor')->nullable();
            $table->unsignedBigInteger('original_grand_total_minor')->nullable();
            $table->unsignedBigInteger('corrected_taxable_base_minor')->nullable();
            $table->unsignedBigInteger('corrected_vat_minor')->nullable();
            $table->unsignedBigInteger('corrected_exempt_minor')->nullable();
            $table->unsignedBigInteger('corrected_out_of_scope_minor')->nullable();
            $table->unsignedBigInteger('corrected_grand_total_minor')->nullable();
            $table->unsignedBigInteger('difference_taxable_base_minor')->nullable();
            $table->unsignedBigInteger('difference_vat_minor')->nullable();
            $table->unsignedBigInteger('difference_exempt_minor')->nullable();
            $table->unsignedBigInteger('difference_out_of_scope_minor')->nullable();
            $table->unsignedBigInteger('difference_grand_total_minor')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->unsignedBigInteger('active_draft_receipt_id')->nullable()->unique();
            $table->string('issue_payload_hash', 64)->nullable();
            $table->string('cancel_payload_hash', 64)->nullable();
            $table->timestamps();

            $table->foreign('active_draft_receipt_id')
                ->references('id')
                ->on('receipts')
                ->restrictOnDelete();
            $table->index(['centre_id', 'status']);
            $table->index(['status', 'issue_date']);
            $table->index(['receipt_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
