<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateWithdrawalUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    public function test_active_affiliate_dashboard_exposes_summary_links_without_mutation_controls(): void
    {
        [$owner] = $this->CreateActiveAffiliate();

        $response = $this->actingAs($owner)->get(route('affiliate.dashboard'));

        $response->assertOk()
            ->assertViewIs('affiliate.dashboard')
            ->assertSee('href="' . route('affiliate.payout-accounts.index') . '"', false)
            ->assertSee('href="' . route('affiliate.withdrawals.index') . '"', false)
            ->assertDontSee('action="' . route('affiliate.payout-accounts.promptpay') . '"', false)
            ->assertDontSee('action="' . route('affiliate.payout-accounts.bank') . '"', false)
            ->assertDontSee('action="' . route('affiliate.withdrawals.store') . '"', false)
            ->assertDontSee('Request withdrawal');
    }

    public function test_affiliate_html_payout_and_withdrawal_forms_redirect_and_update_state(): void
    {
        [$owner, $profile] = $this->CreateActiveAffiliate();

        $payout_response = $this->actingAs($owner)->put(route('affiliate.payout-accounts.promptpay'), [
            'promptpay_identifier' => '081-234-5678',
            'current_password' => 'secret',
        ]);

        $payout_response->assertRedirect(route('affiliate.payout-accounts.index'));
        $this->assertDatabaseHas('affiliate_payout_accounts', [
            'affiliate_profile_id' => $profile->id,
            'payout_type' => 'PROMPTPAY',
            'masked_label' => '******5678',
            'is_active' => 1,
        ]);

        $payout_account = AffiliatePayoutAccount::query()->where('affiliate_profile_id', $profile->id)->firstOrFail();
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            1,
            'affiliate-ui:opening:1',
            null,
            'OPENING_BALANCE',
            'affiliate-ui-correlation-1',
        );

        $withdrawal_response = $this->actingAs($owner)->post(route('affiliate.withdrawals.store'), [
            'amount' => '3600.00',
            'payout_account_id' => $payout_account->id,
            'request_key' => 'affiliate-ui-withdrawal-1',
        ]);

        $withdrawal_response->assertRedirect(route('affiliate.withdrawals.index'));
        $withdrawal = AffiliateWithdrawalRequest::query()->where('request_key', 'affiliate-ui-withdrawal-1')->firstOrFail();
        $this->assertSame('REQUESTED', $withdrawal->status->value);
        $this->assertDatabaseHas('affiliate_wallet_balances', [
            'affiliate_profile_id' => $profile->id,
            'available_balance_minor' => 0,
            'reserved_balance_minor' => 360000,
        ]);
    }

    public function test_dashboard_does_not_render_withdrawal_cancel_forms(): void
    {
        [$owner, $profile] = $this->CreateActiveAffiliate();
        $payout_result = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
            'promptpay_identifier' => '0812345678',
        ], 'affiliate-ui-dashboard-payout');
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($payout_result['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            100000,
            0,
            'affiliate_test',
            4,
            'affiliate-ui:opening:4',
            null,
            'OPENING_BALANCE',
            'affiliate-ui-correlation-5',
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '1000.00',
            $payout_account->id,
            'affiliate-ui-dashboard-withdrawal-1',
            'affiliate-ui-correlation-6',
        );

        $response = $this->actingAs($owner)->get(route('affiliate.dashboard'));

        $response->assertOk()
            ->assertSee('View withdrawals')
            ->assertDontSee('action="' . route('affiliate.withdrawals.cancel', $withdrawal) . '"', false)
            ->assertDontSee('Cancelled by affiliate');
    }

    public function test_html_validation_returns_to_the_originating_finance_page(): void
    {
        [$owner] = $this->CreateActiveAffiliate();

        $payout_response = $this->actingAs($owner)
            ->from(route('affiliate.payout-accounts.index'))
            ->put(route('affiliate.payout-accounts.promptpay'), [
                'promptpay_identifier' => '',
            ]);

        $payout_response->assertRedirect(route('affiliate.payout-accounts.index'))
            ->assertSessionHasErrors('promptpay_identifier');

        $withdrawal_response = $this->actingAs($owner)
            ->from(route('affiliate.withdrawals.index'))
            ->post(route('affiliate.withdrawals.store'), []);

        $withdrawal_response->assertRedirect(route('affiliate.withdrawals.index'))
            ->assertSessionHasErrors(['amount', 'payout_account_id', 'request_key']);
    }

    public function test_admin_withdrawal_page_renders_and_html_transitions_preserve_json_api(): void
    {
        [$owner, $profile] = $this->CreateActiveAffiliate();
        $account_result = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
            'promptpay_identifier' => '0812345678',
        ], 'affiliate-ui-payout-2');
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($account_result['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            2,
            'affiliate-ui:opening:2',
            null,
            'OPENING_BALANCE',
            'affiliate-ui-correlation-2',
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '3600.00',
            $payout_account->id,
            'affiliate-ui-withdrawal-2',
            'affiliate-ui-correlation-3',
        );

        $approver = $this->CreateAdminWithPermission('affiliate-withdrawal-approve');
        $payer = $this->CreateAdminWithPermission('affiliate-withdrawal-pay');
        $list_admin = $this->CreateAdminWithPermission('affiliate-withdrawal-list');
        $list_admin->givePermissionTo('affiliate-withdrawal-pay');

        $page_response = $this->actingAs($list_admin)->get(route('affiliate.withdrawals.admin.index'));
        $page_response->assertOk()
            ->assertViewIs('affiliate.admin.withdrawals')
            ->assertSee('Affiliate withdrawal management')
            ->assertSee('Affiliate Withdrawals')
            ->assertSee('Payment reference')
            ->assertSee((string) $withdrawal->id);

        $json_response = $this->actingAs($list_admin)->getJson(route('affiliate.withdrawals.admin.index'));
        $json_response->assertOk()->assertJsonPath('data.0.id', $withdrawal->id);

        $approve_response = $this->actingAs($approver)->post(route('affiliate.withdrawals.admin.approve', $withdrawal), [
            'transition_key' => 'affiliate-ui-approval-2',
        ]);
        $approve_response->assertRedirect(route('affiliate.withdrawals.admin.index'));
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'APPROVED',
            'approved_by' => $approver->id,
        ]);

        $approved_page_response = $this->actingAs($list_admin)->get(route('affiliate.withdrawals.admin.index'));
        $approved_page_response->assertOk()->assertSee('Bank transaction reference');

        $pay_response = $this->actingAs($payer)->post(route('affiliate.withdrawals.admin.pay', $withdrawal), [
            'transition_key' => 'affiliate-ui-payment-2',
            'payment_reference' => 'bank-ref-ui-2',
        ]);
        $pay_response->assertRedirect(route('affiliate.withdrawals.admin.index'));
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'PAID',
            'paid_by' => $payer->id,
        ]);
    }

    public function test_json_payout_and_withdrawal_endpoints_remain_json_contracts(): void
    {
        [$owner, $profile] = $this->CreateActiveAffiliate();

        $payout_response = $this->actingAs($owner)->putJson(route('affiliate.payout-accounts.promptpay'), [
            'promptpay_identifier' => '0812345678',
            'current_password' => 'secret',
        ]);

        $payout_response->assertOk()->assertJsonPath('payout_type', 'PROMPTPAY');
        $payout_account = AffiliatePayoutAccount::query()->where('affiliate_profile_id', $profile->id)->firstOrFail();
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            3,
            'affiliate-ui:opening:3',
            null,
            'OPENING_BALANCE',
            'affiliate-ui-correlation-4',
        );

        $withdrawal_response = $this->actingAs($owner)->postJson(route('affiliate.withdrawals.store'), [
            'amount' => '3600.00',
            'payout_account_id' => $payout_account->id,
            'request_key' => 'affiliate-ui-withdrawal-json-1',
        ]);

        $withdrawal_response->assertCreated()->assertJsonPath('status', 'REQUESTED');
    }

    private function CreateActiveAffiliate(): array
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $profile = AffiliateProfile::create([
            'user_id' => $owner->id,
            'referral_code' => 'AFF-UI-' . $owner->id,
            'status' => 'ACTIVE',
        ]);

        return [$owner, $profile];
    }

    private function CreateAdminWithPermission(string $permission_name): User
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $admin->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));

        return $admin;
    }
}
