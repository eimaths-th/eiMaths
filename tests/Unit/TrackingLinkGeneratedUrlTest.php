<?php

namespace Tests\Unit;

use App\Http\Controllers\TrackingLinkController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TrackingLinkGeneratedUrlTest extends TestCase
{
    public function test_generated_url_contains_the_immutable_tracking_code(): void
    {
        $controller = new TrackingLinkController();
        $method = new ReflectionMethod($controller, 'buildGeneratedUrl');
        $method->setAccessible(true);

        $url = $method->invoke($controller, 'https://eimaths-th.com/free-trial', [
            'utm_source' => 'facebook',
            'utm_medium' => 'organic_social',
            'utm_campaign' => 'free',
            'tracking_code' => 'trackabc123',
        ]);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('facebook', $query['utm_source']);
        $this->assertSame('organic_social', $query['utm_medium']);
        $this->assertSame('free', $query['utm_campaign']);
        $this->assertSame('trackabc123', $query['tracking_code']);
    }
}
