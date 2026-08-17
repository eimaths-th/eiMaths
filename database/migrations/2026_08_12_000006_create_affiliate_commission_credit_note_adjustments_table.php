<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'affiliate_commission_credit_note_adjustments';

    private const INDEX_IDEMPOTENCY = 'acn_adj_idempotency_unique';

    private const INDEX_CREDIT_EVENT = 'acn_adj_credit_event_unique';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_commission_id');
                $table->foreignId('credit_note_id');
                $table->string('event_type', 20);
                $table->bigInteger('tuition_reversal_delta_minor');
                $table->bigInteger('commission_reversal_delta_minor');
                $table->string('status_before', 32);
                $table->string('status_after', 32);
                $table->string('idempotency_key');
                $table->foreignId('recorded_by')->nullable();
                $table->foreignId('ledger_entry_id')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
                $table->foreign('affiliate_commission_id', 'acn_adj_commission_fk')
                    ->references('id')->on('affiliate_commissions')->restrictOnDelete();
                $table->foreign('credit_note_id', 'acn_adj_credit_note_fk')
                    ->references('id')->on('credit_notes')->restrictOnDelete();
                $table->foreign('recorded_by', 'acn_adj_recorded_by_fk')
                    ->references('id')->on('users')->nullOnDelete();
                $table->foreign('ledger_entry_id', 'acn_adj_ledger_entry_fk')
                    ->references('id')->on('affiliate_ledger_entries')->nullOnDelete();
                $table->unique('idempotency_key', self::INDEX_IDEMPOTENCY);
                $table->unique(['credit_note_id', 'event_type'], self::INDEX_CREDIT_EVENT);
            });
        }

        $this->AddMissingIndexes();
        $this->AddMissingForeignKeys();
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }

    private function AddMissingIndexes(): void
    {
        if (!Schema::hasIndex(self::TABLE, self::INDEX_IDEMPOTENCY)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->unique('idempotency_key', self::INDEX_IDEMPOTENCY);
            });
        }

        if (!Schema::hasIndex(self::TABLE, self::INDEX_CREDIT_EVENT)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->unique(['credit_note_id', 'event_type'], self::INDEX_CREDIT_EVENT);
            });
        }
    }

    private function AddMissingForeignKeys(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $existing_foreign_keys = Schema::getForeignKeys(self::TABLE);
        $definitions = [
            [
                'column' => 'affiliate_commission_id',
                'table' => 'affiliate_commissions',
                'name' => 'acn_adj_commission_fk',
                'delete' => 'restrict',
            ],
            [
                'column' => 'credit_note_id',
                'table' => 'credit_notes',
                'name' => 'acn_adj_credit_note_fk',
                'delete' => 'restrict',
            ],
            [
                'column' => 'recorded_by',
                'table' => 'users',
                'name' => 'acn_adj_recorded_by_fk',
                'delete' => 'null',
            ],
            [
                'column' => 'ledger_entry_id',
                'table' => 'affiliate_ledger_entries',
                'name' => 'acn_adj_ledger_entry_fk',
                'delete' => 'null',
            ],
        ];

        $missing = array_values(array_filter($definitions, static function (array $definition) use ($existing_foreign_keys): bool {
            foreach ($existing_foreign_keys as $foreign_key) {
                if ($foreign_key['columns'] === [$definition['column']]
                    && $foreign_key['foreign_table'] === $definition['table']) {
                    return false;
                }
            }

            return true;
        }));

        if ($missing === []) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($missing): void {
            foreach ($missing as $definition) {
                $foreign_key = $table->foreign($definition['column'], $definition['name'])
                    ->references('id')
                    ->on($definition['table']);

                if ($definition['delete'] === 'null') {
                    $foreign_key->nullOnDelete();
                } else {
                    $foreign_key->restrictOnDelete();
                }
            }
        });
    }
};
