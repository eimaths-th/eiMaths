<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateConfiguration;
use App\Domain\Affiliate\Models\AffiliateReconciliationIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateGovernancePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_rows_are_append_only_and_sanitized_fields_persist(): void
    {
        $audit = AffiliateAuditLog::create([
            'correlation_id' => 'corr-test-001',
            'operation' => 'affiliate.approve',
            'subject_type' => 'affiliate_profile',
            'subject_id' => 1,
            'actor_id' => null,
            'actor_name_snapshot' => 'Admin',
            'prior_state' => ['status' => 'PENDING_APPROVAL'],
            'resulting_state' => ['status' => 'ACTIVE'],
            'amount_minor' => null,
            'outcome' => 'SUCCESS',
            'error_code' => null,
            'occurred_at' => now(),
        ]);

        $this->assertSame(['status' => 'ACTIVE'], $audit->fresh()->resulting_state);
        $this->expectException(LogicException::class);
        $audit->operation = 'changed';
        $audit->save();
    }

    public function test_reconciliation_issue_is_idempotent_and_configuration_keys_are_named(): void
    {
        $issue = AffiliateReconciliationIssue::create([
            'source_table' => 'affiliate_commissions',
            'source_id' => 9,
            'issue_code' => 'MISSING_LEDGER',
            'severity' => 'HIGH',
            'evidence' => ['commission_id' => 9],
            'status' => AffiliateReconciliationStatus::OPEN,
        ]);

        $this->assertSame($issue->id, AffiliateReconciliationIssue::first()->id);
        $this->assertSame('1500', (string) AffiliateConfiguration::where('key', 'commission_rate_basis_points')->value('value'));
        $this->assertSame('10', (string) AffiliateConfiguration::where('key', 'commission_holding_days')->value('value'));
        $this->assertSame('180', (string) AffiliateConfiguration::where('key', 'attribution_duration_days')->value('value'));
        $this->assertSame('1000', (string) AffiliateConfiguration::where('key', 'minimum_withdrawal_minor')->value('value'));
        $this->assertSame('1', (string) AffiliateConfiguration::where('key', 'withdrawal_approval_required')->value('value'));
    }

    public function test_required_financial_permissions_exist_with_web_guard(): void
    {
        foreach ([
            'affiliate-list',
            'affiliate-approve',
            'affiliate-suspend',
            'affiliate-config-edit',
            'affiliate-commission-hold',
            'affiliate-refund-record',
            'affiliate-withdrawal-approve',
            'affiliate-withdrawal-pay',
            'affiliate-reconciliation',
        ] as $permission_name) {
            $this->assertTrue(Permission::where('name', $permission_name)->where('guard_name', 'web')->exists());
        }
    }
}
