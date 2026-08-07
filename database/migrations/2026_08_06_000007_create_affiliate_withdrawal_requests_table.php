<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_withdrawal_requests')) {
            return;
        }

        Schema::create('affiliate_withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles', 'id', 'awr_profile_fk')->restrictOnDelete();
            $table->foreignId('affiliate_payout_account_id')->constrained('affiliate_payout_accounts', 'id', 'awr_payout_account_fk')->restrictOnDelete();
            $table->string('payout_type', 16);
            $table->unsignedBigInteger('amount_minor');
            $table->string('status', 16)->index();
            $table->text('payout_snapshot_encrypted');
            $table->dateTime('requested_at')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id', 'awr_approved_by_fk')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users', 'id', 'awr_paid_by_fk')->nullOnDelete();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users', 'id', 'awr_closed_by_fk')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->string('request_key')->unique();
            $table->string('approval_key')->nullable()->unique();
            $table->string('payment_key')->nullable()->unique();
            $table->string('closure_key')->nullable()->unique();
            $table->timestamps();
            $table->index(['status', 'requested_at']);
            $table->index(['affiliate_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_withdrawal_requests');
    }
};
