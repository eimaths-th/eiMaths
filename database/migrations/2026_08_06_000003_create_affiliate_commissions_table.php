<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_commissions')) {
            return;
        }

        Schema::create('affiliate_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles')->restrictOnDelete();
            $table->foreignId('affiliate_attribution_id')->constrained('affiliate_attributions')->restrictOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('receipt_id')->unique()->constrained('receipts')->restrictOnDelete();
            $table->unsignedBigInteger('eligible_tuition_minor');
            $table->unsignedInteger('rate_basis_points');
            $table->unsignedBigInteger('commission_minor');
            $table->unsignedBigInteger('reversed_minor')->default(0);
            $table->string('status', 32)->index();
            $table->dateTime('release_at')->index();
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('held_at')->nullable();
            $table->text('hold_reason')->nullable();
            $table->string('source_key')->unique();
            $table->timestamps();
            $table->index(['status', 'release_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
