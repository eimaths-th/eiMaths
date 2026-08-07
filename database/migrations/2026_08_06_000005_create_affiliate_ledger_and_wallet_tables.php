<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_ledger_entries')) {
            Schema::create('affiliate_ledger_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles', 'id', 'ale_profile_fk')->restrictOnDelete();
                $table->string('entry_type', 32);
                $table->bigInteger('available_delta_minor');
                $table->bigInteger('reserved_delta_minor');
                $table->bigInteger('balance_available_after_minor');
                $table->unsignedBigInteger('balance_reserved_after_minor');
                $table->string('source_type', 96);
                $table->unsignedBigInteger('source_id');
                $table->string('idempotency_key')->unique();
                $table->foreignId('actor_id')->nullable()->constrained('users', 'id', 'ale_actor_fk')->nullOnDelete();
                $table->string('reason_code', 96);
                $table->dateTime('occurred_at');
                $table->timestamp('created_at')->useCurrent();
                $table->index(['affiliate_profile_id', 'occurred_at']);
                $table->index(['entry_type', 'occurred_at']);
                $table->index(['source_type', 'source_id']);
            });
        }

        if (!Schema::hasTable('affiliate_wallet_balances')) {
            Schema::create('affiliate_wallet_balances', function (Blueprint $table): void {
                $table->foreignId('affiliate_profile_id')->primary()->constrained('affiliate_profiles', 'id', 'awb_profile_fk')->restrictOnDelete();
                $table->bigInteger('available_balance_minor')->default(0);
                $table->unsignedBigInteger('reserved_balance_minor')->default(0);
                $table->foreignId('last_ledger_entry_id')->nullable()->constrained('affiliate_ledger_entries', 'id', 'awb_ledger_entry_fk')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER IF NOT EXISTS affiliate_ledger_reserved_nonnegative_insert BEFORE INSERT ON affiliate_ledger_entries WHEN NEW.balance_reserved_after_minor < 0 BEGIN SELECT RAISE(ABORT, 'reserved balance cannot be negative'); END");
            DB::statement("CREATE TRIGGER IF NOT EXISTS affiliate_ledger_reserved_nonnegative_update BEFORE UPDATE OF balance_reserved_after_minor ON affiliate_ledger_entries WHEN NEW.balance_reserved_after_minor < 0 BEGIN SELECT RAISE(ABORT, 'reserved balance cannot be negative'); END");
            DB::statement("CREATE TRIGGER IF NOT EXISTS affiliate_wallet_reserved_nonnegative_insert BEFORE INSERT ON affiliate_wallet_balances WHEN NEW.reserved_balance_minor < 0 BEGIN SELECT RAISE(ABORT, 'reserved balance cannot be negative'); END");
            DB::statement("CREATE TRIGGER IF NOT EXISTS affiliate_wallet_reserved_nonnegative_update BEFORE UPDATE OF reserved_balance_minor ON affiliate_wallet_balances WHEN NEW.reserved_balance_minor < 0 BEGIN SELECT RAISE(ABORT, 'reserved balance cannot be negative'); END");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS affiliate_wallet_reserved_nonnegative_update');
            DB::statement('DROP TRIGGER IF EXISTS affiliate_wallet_reserved_nonnegative_insert');
            DB::statement('DROP TRIGGER IF EXISTS affiliate_ledger_reserved_nonnegative_update');
            DB::statement('DROP TRIGGER IF EXISTS affiliate_ledger_reserved_nonnegative_insert');
        }
        Schema::dropIfExists('affiliate_wallet_balances');
        Schema::dropIfExists('affiliate_ledger_entries');
    }
};
