<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_payout_accounts') || Schema::hasColumn('affiliate_payout_accounts', 'payout_cooldown_until')) {
            return;
        }

        Schema::table('affiliate_payout_accounts', function (Blueprint $table): void {
            $table->dateTime('payout_cooldown_until')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('affiliate_payout_accounts') || !Schema::hasColumn('affiliate_payout_accounts', 'payout_cooldown_until')) {
            return;
        }

        Schema::table('affiliate_payout_accounts', function (Blueprint $table): void {
            $table->dropColumn('payout_cooldown_until');
        });
    }
};
