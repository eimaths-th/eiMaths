<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliatePayoutDetailsRevealService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateSecurePayoutRevealTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    public function test_payout_details_view_permission_exists_with_web_guard(): void
    {
        $this->assertTrue(Permission::where('name', 'affiliate-payout-details-view')->where('guard_name', 'web')->exists());
    }

    public function test_authorized_promptpay_reveal_uses_immutable_snapshot_and_audits_without_secret(): void
    {
        [$owner, $profile, $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        $actor = $this->CreateAdminWithPermission('affiliate-payout-details-view');

        $result = app(AffiliatePayoutDetailsRevealService::class)->Reveal($withdrawal, $actor, 'corr-reveal-promptpay');

        $this->assertSame('PROMPTPAY', $result['payout_type']);
        $this->assertSame(['promptpay_identifier' => '0812345678'], $result['details']);

        $audit = AffiliateAuditLog::query()->where('operation', 'withdrawal.payout-details.view')->latest('id')->firstOrFail();
        $audit_text = json_encode([$audit->prior_state, $audit->resulting_state], JSON_THROW_ON_ERROR);
        $this->assertSame($actor->id, $audit->actor_id);
        $this->assertSame($withdrawal->id, $audit->subject_id);
        $this->assertSame('corr-reveal-promptpay', $audit->correlation_id);
        $this->assertSame('SUCCESS', $audit->outcome);
        $this->assertStringNotContainsString('0812345678', $audit_text);

        DB::table('affiliate_payout_accounts')->where('id', $withdrawal->affiliate_payout_account_id)->update([
            'details_encrypted' => encrypt(['promptpay_identifier' => '0999999999']),
        ]);
        $repeat = app(AffiliatePayoutDetailsRevealService::class)->Reveal($withdrawal->fresh(), $actor, 'corr-reveal-promptpay-repeat');
        $this->assertSame('0812345678', $repeat['details']['promptpay_identifier']);
    }

    public function test_authorized_bank_reveal_returns_only_transfer_fields(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal('bank');
        $actor = $this->CreateAdminWithPermission('affiliate-payout-details-view');

        $result = app(AffiliatePayoutDetailsRevealService::class)->Reveal($withdrawal, $actor, 'corr-reveal-bank');

        $this->assertSame('BANK', $result['payout_type']);
        $this->assertSame([
            'bank_name' => 'Demo Bank',
            'account_name' => 'Demo User',
            'account_number' => '1234567890',
        ], $result['details']);
        $this->assertSame(['bank_name', 'account_name', 'account_number'], array_keys($result['details']));
    }

    public function test_reveal_rejects_non_approved_withdrawal(): void
    {
        [, , $withdrawal] = $this->CreateRequestedWithdrawal('promptpay');
        $actor = $this->CreateAdminWithPermission('affiliate-payout-details-view');

        try {
            app(AffiliatePayoutDetailsRevealService::class)->Reveal($withdrawal, $actor, 'corr-reveal-requested');
            $this->fail('Expected a state conflict for a withdrawal that is not approved.');
        } catch (AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_STATE_CONFLICT', $exception->ErrorCode());
            $this->assertSame(409, $exception->StatusCode());
        }
    }

    public function test_reveal_rejects_cross_profile_payout_account_reference(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        [$foreign_owner, $foreign_profile] = $this->CreateActiveAffiliate('foreign');
        $foreign_account = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($foreign_owner, [
            'promptpay_identifier' => '0899999999',
        ], 'corr-reveal-foreign-account');
        $actor = $this->CreateAdminWithPermission('affiliate-payout-details-view');

        DB::table('affiliate_withdrawal_requests')->where('id', $withdrawal->id)->update([
            'affiliate_payout_account_id' => $foreign_account['id'],
        ]);

        $this->expectException(AffiliateDomainException::class);
        $this->expectExceptionMessage('not owned');
        app(AffiliatePayoutDetailsRevealService::class)->Reveal($withdrawal->fresh(), $actor, 'corr-reveal-foreign');
    }

    public function test_admin_endpoint_reveals_to_authorized_user_without_email_verification(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        $actor = $this->CreateAdminWithPermission('affiliate-payout-details-view');
        $actor->forceFill(['email_verified_at' => null])->save();

        $response = $this->actingAs($actor)->postJson(
            route('affiliate.withdrawals.admin.payout-details.reveal', $withdrawal),
            [],
            ['X-Affiliate-Correlation-Id' => 'corr-reveal-endpoint'],
        );

        $response->assertOk()
            ->assertJsonPath('data.payout_type', 'PROMPTPAY')
            ->assertJsonPath('data.details.promptpay_identifier', '0812345678')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_admin_without_reveal_permission_and_affiliate_owner_are_forbidden(): void
    {
        [$owner, , $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        $list_admin = $this->CreateAdminWithPermission('affiliate-withdrawal-list');
        $list_admin->forceFill(['email_verified_at' => null])->save();

        $this->actingAs($list_admin)
            ->postJson(route('affiliate.withdrawals.admin.payout-details.reveal', $withdrawal))
            ->assertForbidden();

        $this->actingAs($owner)
            ->postJson(route('affiliate.withdrawals.admin.payout-details.reveal', $withdrawal))
            ->assertForbidden();
    }

    public function test_reveal_route_does_not_require_verified_middleware(): void
    {
        $middleware = app('router')
            ->getRoutes()
            ->getByName('affiliate.withdrawals.admin.payout-details.reveal')
            ->gatherMiddleware();

        $this->assertNotContains('verified', $middleware);
        $this->assertContains('permission:affiliate-payout-details-view', $middleware);
    }

    public function test_admin_list_html_and_json_keep_payout_details_masked_by_default(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        $list_admin = $this->CreateAdminWithPermission('affiliate-withdrawal-list');

        $page_response = $this->actingAs($list_admin)->get(route('affiliate.withdrawals.admin.index'));
        $page_response->assertOk()
            ->assertSee('******5678')
            ->assertDontSee('0812345678');

        $json_response = $this->actingAs($list_admin)->getJson(route('affiliate.withdrawals.admin.index'));
        $json_response->assertOk()
            ->assertJsonMissing(['promptpay_identifier' => '0812345678'])
            ->assertJsonMissingPath('data.0.affiliate_payout_account.details_encrypted');
    }

    public function test_admin_page_only_renders_reveal_control_for_approved_rows_with_permission(): void
    {
        [, , $withdrawal] = $this->CreateApprovedWithdrawal('promptpay');
        $actor = $this->CreateAdminWithPermissions(['affiliate-withdrawal-list', 'affiliate-payout-details-view']);

        $response = $this->actingAs($actor)->get(route('affiliate.withdrawals.admin.index'));

        $response->assertOk()
            ->assertSee('data-reveal-payout-details', false)
            ->assertSee(route('affiliate.withdrawals.admin.payout-details.reveal', $withdrawal), false)
            ->assertDontSee('0812345678');
    }

    private function CreateApprovedWithdrawal(string $type): array
    {
        [$owner, $profile, $withdrawal] = $this->CreateRequestedWithdrawal($type);
        $approver = User::factory()->create(['email_verified_at' => now(), 'password' => Hash::make('secret')]);
        app(AffiliateWithdrawalService::class)->Approve($withdrawal, $approver, 'approval-' . $withdrawal->id, 'corr-approval-' . $withdrawal->id);

        return [$owner, $profile, $withdrawal->fresh()];
    }

    private function CreateRequestedWithdrawal(string $type): array
    {
        [$owner, $profile] = $this->CreateActiveAffiliate($type);
        $account = $type === 'bank'
            ? app(AffiliatePayoutAccountService::class)->UpsertBank($owner, [
                'bank_name' => 'Demo Bank',
                'account_name' => 'Demo User',
                'account_number' => '1234567890',
            ], 'corr-account-bank-' . $owner->id)
            : app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
                'promptpay_identifier' => '0812345678',
            ], 'corr-account-promptpay-' . $owner->id);
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($account['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            $owner->id,
            'reveal:opening:' . $owner->id,
            null,
            'OPENING_BALANCE',
            'corr-opening-' . $owner->id,
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '3600.00',
            $payout_account->id,
            'reveal:withdrawal:' . $owner->id . ':' . $type,
            'corr-request-' . $owner->id,
        );

        return [$owner, $profile, $withdrawal];
    }

    private function CreateActiveAffiliate(string $suffix): array
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $profile = AffiliateProfile::create([
            'user_id' => $owner->id,
            'referral_code' => 'AFF-REVEAL-' . $suffix . '-' . $owner->id,
            'status' => 'ACTIVE',
        ]);

        return [$owner, $profile];
    }

    private function CreateAdminWithPermission(string $permission_name): User
    {
        return $this->CreateAdminWithPermissions([$permission_name]);
    }

    private function CreateAdminWithPermissions(array $permission_names): User
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        foreach ($permission_names as $permission_name) {
            $admin->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        return $admin;
    }
}
