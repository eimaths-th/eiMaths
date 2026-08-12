<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Services\AffiliateReferralCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateReferralCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function GenerateReturnsTheApprovedReferralCodeFormat(): void
    {
        $code = app(AffiliateReferralCodeGenerator::class)->Generate();

        $this->assertMatchesRegularExpression('/\AAFF-[A-Z0-9]{10}\z/', $code);
    }
}
