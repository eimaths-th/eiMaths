<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Services\AffiliatePreflightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateFeatureConfigurationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function EnabledClickTrackingRequiresIndependentVisitorHashKey(): void
    {
        Config::set('affiliate.features.click_tracking', true);
        Config::set('affiliate.visitor_hash_key', null);

        $check = $this->FindCheck('visitor_hash_key');

        $this->assertSame('BLOCKED', $check['status']);
    }

    #[Test]
    public function DisabledClickTrackingDoesNotRequireVisitorHashKey(): void
    {
        Config::set('affiliate.features.click_tracking', false);
        Config::set('affiliate.visitor_hash_key', null);

        $check = $this->FindCheck('visitor_hash_key');

        $this->assertNotSame('BLOCKED', $check['status']);
    }

    #[Test]
    public function EmployeeEnrollmentIsDisabledByDefaultInTests(): void
    {
        $this->assertFalse((bool) config('affiliate.features.employee_enrollment'));
    }

    #[Test]
    public function EmployeeEnrollmentUsesTheApprovedRoleAndRateLimitConfiguration(): void
    {
        $this->assertSame(
            ['Admin', 'SystemAdmin', 'BranchAdmin', 'Teacher', 'blog'],
            config('affiliate.employee_roles')
        );
        $this->assertSame(6, (int) config('affiliate.abuse.employee_enrollment_attempts_per_minute'));
    }

    #[Test]
    public function EnabledMediaLibraryRequiresDestinationHost(): void
    {
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.allowed_destination_hosts', []);

        $check = $this->FindCheck('destination_hosts');

        $this->assertSame('BLOCKED', $check['status']);
    }

    private function FindCheck(string $name): array
    {
        $checks = app(AffiliatePreflightService::class)->Checks();

        foreach ($checks as $check) {
            if ($check['name'] === $name) {
                return $check;
            }
        }

        $this->fail("Missing Affiliate preflight check: {$name}");
    }
}
