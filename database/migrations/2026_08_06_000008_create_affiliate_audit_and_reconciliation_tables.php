<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_audit_logs')) {
            Schema::create('affiliate_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('correlation_id', 96)->index();
                $table->string('operation', 128);
                $table->string('subject_type', 128);
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users', 'id', 'aal_actor_fk')->nullOnDelete();
                $table->string('actor_name_snapshot', 255)->nullable();
                $table->json('prior_state')->nullable();
                $table->json('resulting_state')->nullable();
                $table->bigInteger('amount_minor')->nullable();
                $table->string('outcome', 32);
                $table->string('error_code', 128)->nullable();
                $table->dateTime('occurred_at');
                $table->timestamps();
                $table->index(['subject_type', 'subject_id']);
            });
        }

        if (!Schema::hasTable('affiliate_reconciliation_issues')) {
            Schema::create('affiliate_reconciliation_issues', function (Blueprint $table): void {
                $table->id();
                $table->string('source_table', 128);
                $table->unsignedBigInteger('source_id');
                $table->string('issue_code', 128);
                $table->string('severity', 32);
                $table->json('evidence')->nullable();
                $table->string('status', 32)->index();
                $table->foreignId('resolved_by')->nullable()->constrained('users', 'id', 'ari_resolved_by_fk')->nullOnDelete();
                $table->dateTime('resolved_at')->nullable();
                $table->text('resolution_note')->nullable();
                $table->foreignId('adjustment_ledger_entry_id')->nullable()->constrained('affiliate_ledger_entries', 'id', 'ari_adjustment_entry_fk')->nullOnDelete();
                $table->timestamps();
                $table->unique(['source_table', 'source_id', 'issue_code'], 'ari_source_issue_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_reconciliation_issues');
        Schema::dropIfExists('affiliate_audit_logs');
    }
};
