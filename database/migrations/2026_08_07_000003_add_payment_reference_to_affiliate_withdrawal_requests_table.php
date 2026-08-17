<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_withdrawal_requests') || Schema::hasColumn('affiliate_withdrawal_requests', 'payment_reference')) {
            return;
        }

        Schema::table('affiliate_withdrawal_requests', function (Blueprint $table): void {
            $table->string('payment_reference', 255)->nullable()->after('payment_key');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('affiliate_withdrawal_requests') || !Schema::hasColumn('affiliate_withdrawal_requests', 'payment_reference')) {
            return;
        }

        Schema::table('affiliate_withdrawal_requests', function (Blueprint $table): void {
            $table->dropColumn('payment_reference');
        });
    }
};
