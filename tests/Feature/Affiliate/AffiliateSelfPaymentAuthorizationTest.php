<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateSelfPaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Department::create(['id' => 1, 'name' => 'HQ test', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        \App\Models\Department::forceCreate(['id' => 5, 'name' => 'Employee branch', 'code' => 'EMP', 'status' => 1]);
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    public function test_self_payment_permission_exists_with_web_guard(): void
    {
        $this->assertTrue(Permission::where('name', 'affiliate-withdrawal-pay-self')->where('guard_name', 'web')->exists());
    }

    public function test_payment_requires_external_transfer_reference(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal();
        $payer = $this->CreateFinanceActor();
        $payer->givePermissionTo(Permission::findOrCreate('affiliate-withdrawal-pay', 'web'));

        $response = $this->actingAs($payer)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'payment-reference-required-1',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('payment_reference');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'APPROVED',
            'paid_by' => null,
            'payment_key' => null,
        ]);
        $this->assertSame(0, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->where('source_id', $withdrawal->id)->count());
    }

    public function test_same_actor_payment_without_self_permission_keeps_existing_actor_separation(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $approver->givePermissionTo(Permission::findOrCreate('affiliate-withdrawal-pay', 'web'));
        $reserved_before = (int) DB::table('affiliate_wallet_balances')->where('affiliate_profile_id', $withdrawal->affiliate_profile_id)->value('reserved_balance_minor');

        $response = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'self-payment-denied-1',
            'payment_reference' => 'bank-ref-self-denied-1',
        ]);

        $response->assertStatus(409)->assertJsonPath('error_code', 'AFFILIATE_ACTOR_SEPARATION');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'APPROVED',
            'paid_by' => null,
        ]);
        $this->assertSame($reserved_before, (int) DB::table('affiliate_wallet_balances')->where('affiliate_profile_id', $withdrawal->affiliate_profile_id)->value('reserved_balance_minor'));
        $this->assertSame(0, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->count());
    }

    public function test_self_payment_permission_inherited_from_broad_role_is_not_sufficient(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $admin_role = Role::findOrCreate('Admin', 'web');
        $admin_role->givePermissionTo([
            Permission::findOrCreate('affiliate-withdrawal-pay', 'web'),
            Permission::findOrCreate('affiliate-withdrawal-pay-self', 'web'),
        ]);
        $approver->assignRole($admin_role);

        $this->assertTrue($approver->can('affiliate-withdrawal-pay-self'));

        $response = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'self-payment-inherited-role-1',
            'payment_reference' => 'bank-ref-inherited-role-1',
        ]);

        $response->assertStatus(409)->assertJsonPath('error_code', 'AFFILIATE_ACTOR_SEPARATION');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'APPROVED',
            'paid_by' => null,
        ]);
    }

    public function test_same_actor_payment_with_self_permission_succeeds_and_records_sanitized_audit(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $approver->givePermissionTo([
            Permission::findOrCreate('affiliate-withdrawal-pay', 'web'),
            Permission::findOrCreate('affiliate-withdrawal-pay-self', 'web'),
        ]);

        $response = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'self-payment-allowed-1',
            'payment_reference' => 'bank-ref-self-allowed-1',
        ]);

        $response->assertOk()->assertJsonPath('status', 'PAID');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'PAID',
            'approved_by' => $approver->id,
            'paid_by' => $approver->id,
        ]);
        $this->assertSame(1, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->where('source_id', $withdrawal->id)->count());

        $audit = AffiliateAuditLog::query()->where('operation', 'withdrawal.self-pay')->where('subject_id', $withdrawal->id)->firstOrFail();
        $this->assertSame($approver->id, $audit->actor_id);
        $this->assertSame('SUCCESS', $audit->outcome);
        $this->assertSame(true, $audit->resulting_state['self_payment']);
        $this->assertStringNotContainsString('0812345678', json_encode([$audit->prior_state, $audit->resulting_state], JSON_THROW_ON_ERROR));
    }

    public function test_different_actor_can_pay_with_existing_permission_only(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $payer = $this->CreateFinanceActor();
        $payer->givePermissionTo(Permission::findOrCreate('affiliate-withdrawal-pay', 'web'));

        $response = $this->actingAs($payer)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'different-payment-1',
            'payment_reference' => 'bank-ref-different-1',
        ]);

        $response->assertOk()->assertJsonPath('status', 'PAID');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'approved_by' => $approver->id,
            'paid_by' => $payer->id,
            'payment_reference' => 'bank-ref-different-1',
        ]);
        $this->assertSame(0, AffiliateAuditLog::query()->where('operation', 'withdrawal.self-pay')->where('subject_id', $withdrawal->id)->count());
    }

    public function test_allowed_self_payment_is_idempotent_without_duplicate_ledger_or_audit(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $approver->givePermissionTo([
            Permission::findOrCreate('affiliate-withdrawal-pay', 'web'),
            Permission::findOrCreate('affiliate-withdrawal-pay-self', 'web'),
        ]);
        $payload = [
            'transition_key' => 'self-payment-idempotent-1',
            'payment_reference' => 'bank-ref-idempotent-1',
        ];

        $first = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), $payload);
        $second = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), $payload);

        $first->assertOk()->assertJsonPath('status', 'PAID');
        $second->assertOk()->assertJsonPath('status', 'PAID');
        $this->assertSame(1, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->where('source_id', $withdrawal->id)->count());
        $this->assertSame(1, AffiliateAuditLog::query()->where('operation', 'withdrawal.self-pay')->where('subject_id', $withdrawal->id)->count());
    }

    public function test_reusing_payment_key_with_different_reference_is_rejected(): void
    {
        [, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $approver->givePermissionTo([
            Permission::findOrCreate('affiliate-withdrawal-pay', 'web'),
            Permission::findOrCreate('affiliate-withdrawal-pay-self', 'web'),
        ]);

        $first = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'payment-reference-collision-1',
            'payment_reference' => 'bank-ref-collision-1',
        ]);
        $second = $this->actingAs($approver)->postJson(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'payment-reference-collision-1',
            'payment_reference' => 'bank-ref-collision-2',
        ]);

        $first->assertOk();
        $second->assertStatus(409)->assertJsonPath('error_code', 'AFFILIATE_IDEMPOTENCY_CONFLICT');
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'payment_reference' => 'bank-ref-collision-1',
        ]);
        $this->assertSame(1, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->where('source_id', $withdrawal->id)->count());
    }

    public function test_self_payment_audit_failure_rolls_back_paid_state_and_ledger(): void
    {
        [$owner, , $withdrawal, $approver] = $this->CreateApprovedWithdrawal();
        $approver->givePermissionTo([
            Permission::findOrCreate('affiliate-withdrawal-pay', 'web'),
            Permission::findOrCreate('affiliate-withdrawal-pay-self', 'web'),
        ]);
        $wallet_service = app(AffiliateWalletService::class);
        app()->instance(AffiliateWalletService::class, $wallet_service);
        $audit_service = Mockery::mock(AffiliateAuditService::class);
        $audit_service->shouldReceive('Record')
            ->once()
            ->withArgs(fn (...$arguments): bool => ($arguments[0] ?? null) === 'withdrawal.self-pay')
            ->andThrow(new RuntimeException('audit unavailable'));
        app()->instance(AffiliateAuditService::class, $audit_service);

        try {
            app(AffiliateWithdrawalService::class)->Pay($withdrawal, $approver, 'bank-ref-audit-failure-1', 'self-payment-audit-failure-1', 'corr-self-payment-failure');
            $this->fail('Expected the audit failure to abort the payment transaction.');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseHas('affiliate_withdrawal_requests', ['id' => $withdrawal->id, 'status' => 'APPROVED', 'paid_by' => null]);
        $this->assertSame(0, DB::table('affiliate_ledger_entries')->where('entry_type', 'WITHDRAWAL_PAID')->where('source_id', $withdrawal->id)->count());
        $this->assertSame(360000, (int) DB::table('affiliate_wallet_balances')->where('affiliate_profile_id', $owner->AffiliateProfile()->value('id'))->value('reserved_balance_minor'));
    }

    private function CreateApprovedWithdrawal(): array
    {
        $owner = $this->CreateUser();
        $profile = AffiliateProfile::create([
            'user_id' => $owner->id,
            'referral_code' => 'AFF-SELF-PAY-' . $owner->id,
            'status' => 'ACTIVE',
        ]);
        $account_result = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
            'promptpay_identifier' => '0812345678',
        ], 'self-payment-account-' . $owner->id);
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($account_result['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            $owner->id,
            'self-payment-opening-' . $owner->id,
            null,
            'OPENING_BALANCE',
            'corr-self-payment-opening-' . $owner->id,
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '3600.00',
            $payout_account->id,
            'self-payment-request-' . $owner->id,
            'corr-self-payment-request-' . $owner->id,
        );
        $approver = $this->CreateFinanceActor();
        app(AffiliateWithdrawalService::class)->Approve($withdrawal, $approver, 'self-payment-approval-' . $owner->id, 'corr-self-payment-approval-' . $owner->id);

        return [$owner, $profile, $withdrawal->fresh(), $approver];
    }

    private function CreateFinanceActor(): User
    {
        $actor = User::factory()->create(['department_id' => 1, 'email_verified_at' => now(), 'password' => Hash::make('secret')]);
        $actor->assignRole(Role::findOrCreate('AffiliateFinance', 'web'));
        return $actor;
    }

    private function CreateUser(): User
    {
        return User::factory()->create([
            'department_id' => 5,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
    }
}
