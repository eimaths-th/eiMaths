<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Services\AffiliateConfigurationService;
use App\Domain\Affiliate\Services\AffiliateFeatureGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AffiliateConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_settings_are_read_as_typed_values_and_zero_is_valid(): void
    {
        $service = app(AffiliateConfigurationService::class);

        $this->assertSame(1500, $service->CommissionRateBasisPoints());
        $this->assertSame(10, $service->CommissionHoldingDays());
        $this->assertSame(180, $service->AttributionDurationDays());
        $this->assertSame(1000, $service->MinimumWithdrawalMinor());
        $this->assertTrue($service->IsWithdrawalApprovalRequired());

        DB::table('affiliate_configurations')
            ->where('key', 'commission_holding_days')
            ->update(['value' => '0']);
        $this->assertSame(0, app(AffiliateConfigurationService::class)->CommissionHoldingDays());
    }

    public function test_missing_duplicate_and_invalid_settings_fail_closed(): void
    {
        DB::table('affiliate_configurations')->where('key', 'commission_rate_basis_points')->delete();
        $this->expectException(AffiliateDomainException::class);
        app(AffiliateConfigurationService::class)->CommissionRateBasisPoints();
    }

    public function test_duplicate_setting_is_not_silently_selected(): void
    {
        $row = DB::table('affiliate_configurations')->where('key', 'commission_holding_days')->first();
        DB::table('affiliate_configurations')->insert([
            'key' => 'commission_holding_days',
            'value' => '10',
            'lms_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(AffiliateDomainException::class);
        app(AffiliateConfigurationService::class)->CommissionHoldingDays();
    }

    public function test_feature_gate_is_disabled_by_default_and_rejects_unknown_features(): void
    {
        $gate = app(AffiliateFeatureGate::class);
        $this->assertFalse($gate->IsEnabled('commission_creation'));
        $this->expectException(AffiliateDomainException::class);
        $gate->AssertEnabled('unknown_feature');
    }
}
