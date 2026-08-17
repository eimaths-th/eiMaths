<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Services\AffiliateDestinationValidator;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateDestinationValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.allowed_destination_hosts', ['example.com', 'shop.example.com']);
    }

    #[Test]
    public function AcceptsRelativePathsAndExactHttpsHosts(): void
    {
        $validator = app(AffiliateDestinationValidator::class);

        $this->assertSame('/courses/math?level=1', $validator->Normalize('/courses/math?level=1'));
        $this->assertSame('https://example.com/landing', $validator->Normalize('https://example.com/landing'));
        $this->assertSame('https://shop.example.com/offer', $validator->Normalize('https://shop.example.com/offer'));
    }

    #[Test]
    public function RejectsOpenRedirectAndControlCharacterTargets(): void
    {
        $validator = app(AffiliateDestinationValidator::class);

        foreach ([
            '',
            '//evil.example/path',
            '/\\evil',
            "/safe\nheader",
            'http://example.com/landing',
            'javascript:alert(1)',
            'https://user:pass@example.com/landing',
            'https://evil.example.com/landing',
            'https://example.com/landing#bad' . "\n",
        ] as $destination) {
            try {
                $validator->Normalize($destination);
                $this->fail('Unsafe destination should be rejected: ' . json_encode($destination));
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
