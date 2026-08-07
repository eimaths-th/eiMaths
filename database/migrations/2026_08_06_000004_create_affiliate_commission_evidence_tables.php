<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_commission_source_items')) {
            Schema::create('affiliate_commission_source_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_commission_id')->constrained('affiliate_commissions', 'id', 'acsi_commission_fk')->restrictOnDelete();
                $table->foreignId('order_detail_list_id')->constrained('order_detail_lists', 'id', 'acsi_order_detail_fk')->restrictOnDelete();
                $table->unsignedBigInteger('tuition_minor');
                $table->timestamps();
                $table->unique(['affiliate_commission_id', 'order_detail_list_id'], 'acsi_commission_order_detail_uq');
            });
        } else {
            $foreign_keys = collect(Schema::getForeignKeys('affiliate_commission_source_items'))->pluck('name');

            Schema::table('affiliate_commission_source_items', function (Blueprint $table) use ($foreign_keys): void {
                if (!$foreign_keys->contains('acsi_commission_fk')) {
                    $table->foreign('affiliate_commission_id', 'acsi_commission_fk')
                        ->references('id')->on('affiliate_commissions')->restrictOnDelete();
                }
                if (!$foreign_keys->contains('acsi_order_detail_fk')) {
                    $table->foreign('order_detail_list_id', 'acsi_order_detail_fk')
                        ->references('id')->on('order_detail_lists')->restrictOnDelete();
                }
            });

            if (!Schema::hasIndex('affiliate_commission_source_items', 'acsi_commission_order_detail_uq')) {
                Schema::table('affiliate_commission_source_items', function (Blueprint $table): void {
                    $table->unique(['affiliate_commission_id', 'order_detail_list_id'], 'acsi_commission_order_detail_uq');
                });
            }
        }

        if (!Schema::hasTable('affiliate_commission_reversals')) {
            Schema::create('affiliate_commission_reversals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_commission_id')->constrained('affiliate_commissions', 'id', 'acr_commission_fk')->restrictOnDelete();
                $table->string('external_refund_reference')->unique('acr_external_ref_uq');
                $table->unsignedBigInteger('refunded_tuition_minor');
                $table->unsignedBigInteger('reversal_minor');
                $table->string('idempotency_key')->unique('acr_idempotency_uq');
                $table->foreignId('recorded_by')->nullable()->constrained('users', 'id', 'acr_recorded_by_fk')->nullOnDelete();
                $table->text('reason');
                $table->dateTime('occurred_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commission_reversals');
        Schema::dropIfExists('affiliate_commission_source_items');
    }
};
