<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Services\AffiliateVisitorHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateVisitorHasherTest extends TestCase
{
    #[Test]
    public function HashesNormalizedRequestDeterministicallyWithoutRawSourceText(): void
    {
        Config::set('affiliate.visitor_hash_key', 'visitor-key-with-at-least-32-characters-123');
        $request = Request::create('/r/AFF-001', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => " Example\tBrowser\n 1.0 ",
        ]);

        $hash = app(AffiliateVisitorHasher::class)->HashRequest($request);

        $this->assertSame(64, strlen($hash));
        $this->assertSame($hash, app(AffiliateVisitorHasher::class)->HashRequest($request));
        $this->assertStringNotContainsString('203.0.113.10', $hash);
        $this->assertStringNotContainsString('example', strtolower($hash));
    }

    #[Test]
    public function KeyRotationChangesTheDigestAndInvalidKeysAreRejected(): void
    {
        $request = Request::create('/r/AFF-001', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.10']);
        Config::set('affiliate.visitor_hash_key', 'visitor-key-with-at-least-32-characters-123');
        $first = app(AffiliateVisitorHasher::class)->HashRequest($request);
        Config::set('affiliate.visitor_hash_key', 'rotated-visitor-key-with-at-least-32-characters-456');
        $second = app(AffiliateVisitorHasher::class)->HashRequest($request);
        $this->assertNotSame($first, $second);

        Config::set('affiliate.visitor_hash_key', 'short-key');
        $this->expectException(InvalidArgumentException::class);
        app(AffiliateVisitorHasher::class)->HashRequest($request);
    }
}
