<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.analytics', true);
    }

    public function test_unauthenticated_and_unverified_users_are_rejected(): void
    {
        $this->getJson(route('affiliate.analytics.data', [
            'from' => '2026-08-01',
            'to' => '2026-08-03',
        ]))->assertUnauthorized();

        $user = User::factory()->unverified()->create(['password' => Hash::make('secret')]);
        $this->actingAs($user)->getJson(route('affiliate.analytics.data', [
            'from' => '2026-08-01',
            'to' => '2026-08-03',
        ]))->assertForbidden();
    }

    public function test_active_owner_receives_stable_zero_contract(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ANALYTICS-API',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        $response = $this->actingAs($user)->getJson(route('affiliate.analytics.data', [
            'from' => '2026-08-01',
            'to' => '2026-08-03',
        ]));

        $response->assertOk()
            ->assertJsonPath('data.range.from', '2026-08-01')
            ->assertJsonPath('data.range.timezone', 'Asia/Bangkok')
            ->assertJsonPath('data.kpis.total_clicks', 0)
            ->assertJsonPath('data.kpis.eligible_sales_value', '0.00')
            ->assertJsonPath('data.kpis.conversion_rate', '0.00')
            ->assertJsonCount(3, 'data.daily_series')
            ->assertJsonPath('data.sales_pagination.current_page', 1);
    }

    public function test_invalid_ranges_return_localized_validation_errors(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ANALYTICS-VALIDATION',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        $this->actingAs($user)->getJson(route('affiliate.analytics.data', [
            'from' => '2026-08-04',
            'to' => '2026-08-03',
        ]))->assertStatus(422)
            ->assertJsonValidationErrors(['to'])
            ->assertJsonPath('errors.to.0', 'กรุณาเลือกวันเริ่มต้นที่ไม่เกินวันสิ้นสุด');
    }

    public function test_disabled_analytics_feature_is_not_exposed(): void
    {
        Config::set('affiliate.features.analytics', false);
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ANALYTICS-OFF',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        $this->actingAs($user)->getJson(route('affiliate.analytics.data', [
            'from' => '2026-08-01',
            'to' => '2026-08-03',
        ]))->assertStatus(503);
    }
}
