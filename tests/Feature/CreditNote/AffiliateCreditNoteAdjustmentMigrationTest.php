<?php

namespace Tests\Feature\CreditNote;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateCreditNoteAdjustmentMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function MigrationCreatesRecoverableAdjustmentSchema(): void
    {
        $this->assertTrue(Schema::hasIndex(
            'affiliate_commission_credit_note_adjustments',
            'acn_adj_idempotency_unique',
        ));
        $this->assertTrue(Schema::hasIndex(
            'affiliate_commission_credit_note_adjustments',
            'acn_adj_credit_event_unique',
        ));

        $foreign_keys = Schema::getForeignKeys('affiliate_commission_credit_note_adjustments');
        $this->assertTrue($this->HasForeignKey($foreign_keys, 'affiliate_commission_id', 'affiliate_commissions'));
        $this->assertTrue($this->HasForeignKey($foreign_keys, 'credit_note_id', 'credit_notes'));
        $this->assertTrue($this->HasForeignKey($foreign_keys, 'recorded_by', 'users'));
        $this->assertTrue($this->HasForeignKey($foreign_keys, 'ledger_entry_id', 'affiliate_ledger_entries'));
    }

    private function HasForeignKey(array $foreign_keys, string $column, string $table): bool
    {
        foreach ($foreign_keys as $foreign_key) {
            if ($foreign_key['columns'] === [$column] && $foreign_key['foreign_table'] === $table) {
                return true;
            }
        }

        return false;
    }
}
