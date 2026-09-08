<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateFinanceNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.features.employee_enrollment', true);
    }

    public function test_active_affiliate_can_render_dashboard_payout_and_withdrawal_pages(): void
    {
        [$owner, $profile] = $this->CreateActiveAffiliate('AFF-NAV-OWNER-');
        $payout_result = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
            'promptpay_identifier' => '0812345678',
        ], 'affiliate-navigation-payout-1');
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($payout_result['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            360000,
            0,
            'affiliate_test',
            1,
            'affiliate-navigation:opening:1',
            null,
            'OPENING_BALANCE',
            'affiliate-navigation-correlation-1',
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '1000.00',
            $payout_account->id,
            'affiliate-navigation-withdrawal-1',
            'affiliate-navigation-correlation-2',
        );

        $dashboard_response = $this->actingAs($owner)->get(route('affiliate.dashboard'));
        $dashboard_response->assertOk()->assertViewIs('affiliate.dashboard');

        $payout_response = $this->actingAs($owner)->get(route('affiliate.payout-accounts.index'));
        $payout_response->assertOk()
            ->assertViewIs('affiliate.payout_accounts.index')
            ->assertSee('******5678');

        $withdrawal_response = $this->actingAs($owner)->get(route('affiliate.withdrawals.index'));
        $withdrawal_response->assertOk()
            ->assertViewIs('affiliate.withdrawals.index')
            ->assertSeeInOrder([
                trans('affiliate.withdrawals.available_balance', [], 'th'),
                '2,600.00',
                trans('affiliate.withdrawals.reserved_balance', [], 'th'),
                '1,000.00',
            ])
            ->assertSee('10.00')
            ->assertSee((string) $payout_account->id)
            ->assertSee((string) $withdrawal->id);
    }

    public function test_withdrawal_page_renders_zero_wallet_balances_when_projection_is_missing(): void
    {
        [$owner] = $this->CreateActiveAffiliate('AFF-NAV-NO-WALLET-');

        $response = $this->actingAs($owner)->get(route('affiliate.withdrawals.index'));

        $response->assertOk()
            ->assertViewIs('affiliate.withdrawals.index')
            ->assertSeeInOrder([
                trans('affiliate.withdrawals.available_balance', [], 'th'),
                '0.00',
                trans('affiliate.withdrawals.reserved_balance', [], 'th'),
                '0.00',
            ]);
    }

    public function test_inactive_affiliate_cannot_render_finance_pages(): void
    {
        [$owner] = $this->CreateActiveAffiliate('AFF-NAV-INACTIVE-');
        $owner->AffiliateProfile()->update(['status' => 'SUSPENDED']);

        foreach ([
            'affiliate.dashboard',
            'affiliate.payout-accounts.index',
            'affiliate.withdrawals.index',
        ] as $route_name) {
            $response = $this->actingAs($owner)->get(route($route_name));

            $response->assertRedirect(route('affiliate.waiting'));
        }
    }

    public function test_finance_pages_only_expose_the_authenticated_affiliate_profile(): void
    {
        [$owner, $owner_profile] = $this->CreateActiveAffiliate('AFF-NAV-FIRST-');
        [$other_owner] = $this->CreateActiveAffiliate('AFF-NAV-SECOND-');

        $payout_result = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, [
            'promptpay_identifier' => '0898765432',
        ], 'affiliate-navigation-payout-2');
        $payout_account = AffiliatePayoutAccount::query()->findOrFail($payout_result['id']);
        app(AffiliateWalletService::class)->ApplyEntry(
            $owner_profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            100000,
            0,
            'affiliate_test',
            2,
            'affiliate-navigation:opening:2',
            null,
            'OPENING_BALANCE',
            'affiliate-navigation-correlation-3',
        );
        $withdrawal = app(AffiliateWithdrawalService::class)->RequestWithdrawal(
            $owner,
            '1000.00',
            $payout_account->id,
            'affiliate-navigation-withdrawal-2',
            'affiliate-navigation-correlation-4',
        );

        $payout_response = $this->actingAs($other_owner)->get(route('affiliate.payout-accounts.index'));
        $payout_response->assertOk()
            ->assertViewIs('affiliate.payout_accounts.index')
            ->assertDontSee('******5432');

        $withdrawal_response = $this->actingAs($other_owner)->get(route('affiliate.withdrawals.index'));
        $withdrawal_response->assertOk()
            ->assertViewIs('affiliate.withdrawals.index')
            ->assertDontSee('REQUESTED');
    }

    public function test_active_affiliate_navigation_contains_separate_finance_entries(): void
    {
        [$owner] = $this->CreateActiveAffiliate('AFF-NAV-MENU-');

        $dashboard_response = $this->actingAs($owner)->get(route('affiliate.dashboard'));
        $dashboard_response->assertOk()
            ->assertSee('href="' . route('affiliate.dashboard') . '"', false)
            ->assertSee('href="' . route('affiliate.payout-accounts.index') . '"', false)
            ->assertSee('href="' . route('affiliate.withdrawals.index') . '"', false);

        $frontend_header = view('affiliate.frontend.header')->render();
        $this->assertStringContainsString('href="' . route('affiliate.dashboard') . '"', $frontend_header);
        $this->assertStringContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $frontend_header);
        $this->assertStringContainsString('href="' . route('affiliate.withdrawals.index') . '"', $frontend_header);
    }

    public function test_inactive_affiliate_navigation_does_not_render_finance_entries(): void
    {
        [$owner] = $this->CreateActiveAffiliate('AFF-NAV-HIDDEN-');
        $owner->AffiliateProfile()->update(['status' => 'SUSPENDED']);

        $this->actingAs($owner);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $header = view('affiliate.frontend.header')->render();

        $this->assertStringNotContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $menu);
        $this->assertStringNotContainsString('href="' . route('affiliate.withdrawals.index') . '"', $menu);
        $this->assertStringNotContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $header);
        $this->assertStringNotContainsString('href="' . route('affiliate.withdrawals.index') . '"', $header);
    }

    #[Test]
    public function ActiveUnverifiedEmployeeNavigationMatchesPortalAccess(): void
    {
        [$employee] = $this->CreateEmployeeAffiliate(false, 'AFF-NAV-EMPLOYEE-');

        $dashboard_response = $this->actingAs($employee)->get(route('affiliate.dashboard'));
        $dashboard_response->assertOk()
            ->assertSee('href="' . route('affiliate.payout-accounts.index') . '"', false)
            ->assertSee('href="' . route('affiliate.withdrawals.index') . '"', false);

        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $header = view('affiliate.frontend.header')->render();
        $navigation = view('affiliate.partials.navigation')->render();

        foreach ([$menu, $header, $navigation] as $rendered_view) {
            $this->assertStringContainsString('href="' . route('affiliate.dashboard') . '"', $rendered_view);
            $this->assertStringContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $rendered_view);
            $this->assertStringContainsString('href="' . route('affiliate.withdrawals.index') . '"', $rendered_view);
        }
    }

    #[Test]
    public function UnverifiedExternalNavigationDoesNotAdvertiseAffiliateMemberRoutes(): void
    {
        [$external_user] = $this->CreateExternalAffiliate(false, 'AFF-NAV-UNVERIFIED-');

        $this->actingAs($external_user);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $header = view('affiliate.frontend.header')->render();

        foreach ([$menu, $header] as $rendered_view) {
            $this->assertStringNotContainsString('href="' . route('affiliate.dashboard') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.withdrawals.index') . '"', $rendered_view);
        }
    }

    #[Test]
    public function PendingEmployeeNavigationShowsOnlyEmployeeStatusEntryPoint(): void
    {
        [$employee] = $this->CreateEmployeeAffiliate(false, 'AFF-NAV-PENDING-');
        $employee->AffiliateProfile()->update(['status' => 'PENDING_APPROVAL']);

        $this->actingAs($employee);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $header = view('affiliate.frontend.header')->render();

        foreach ([$menu, $header] as $rendered_view) {
            $this->assertStringContainsString('href="' . route('affiliate.employee-access.show') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.dashboard') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.withdrawals.index') . '"', $rendered_view);
        }
    }

    #[Test]
    public function DisabledWalletFeatureHidesAffiliateFinanceDestinations(): void
    {
        [$external_user] = $this->CreateExternalAffiliate(true, 'AFF-NAV-DISABLED-');
        Config::set('affiliate.features.wallet_withdrawals', false);

        $this->actingAs($external_user);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $header = view('affiliate.frontend.header')->render();

        foreach ([$menu, $header] as $rendered_view) {
            $this->assertStringNotContainsString('href="' . route('affiliate.dashboard') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.payout-accounts.index') . '"', $rendered_view);
            $this->assertStringNotContainsString('href="' . route('affiliate.withdrawals.index') . '"', $rendered_view);
        }
    }

    private function CreateEmployeeAffiliate(bool $verified, string $referral_prefix): array
    {
        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
            'password' => Hash::make('secret'),
        ]);
        $role = Role::findOrCreate('Teacher', 'web');
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('affiliate-user', 'web'));
        $user->assignRole($role);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_prefix . $user->id,
            'status' => 'ACTIVE',
        ]);

        return [$user->fresh(), $profile->fresh()];
    }

    private function CreateExternalAffiliate(bool $verified, string $referral_prefix): array
    {
        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
            'password' => Hash::make('secret'),
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_prefix . $user->id,
            'status' => 'ACTIVE',
        ]);

        return [$user->fresh(), $profile->fresh()];
    }

    private function CreateActiveAffiliate(string $referral_prefix): array
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $owner->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        $profile = AffiliateProfile::create([
            'user_id' => $owner->id,
            'referral_code' => $referral_prefix . $owner->id,
            'status' => 'ACTIVE',
        ]);

        return [$owner, $profile];
    }
}
