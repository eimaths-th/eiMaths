<?php

namespace Tests\Unit;

use App\Models\TrackingLink;
use App\Services\CrmLeadNotificationService;
use App\Services\CrmLeadConsentService;
use App\Services\CrmLeadService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TrackingLinkAttributionTest extends TestCase
{
    public function test_resolved_link_overrides_untrusted_utm_and_snapshots_the_creator(): void
    {
        $trackingLink = (new TrackingLink())->forceFill([
            'id' => 15,
            'tracking_code' => 'trackabc123',
            'name' => 'Facebook Free Trial',
            'utm_source' => 'facebook',
            'utm_medium' => 'organic_social',
            'utm_campaign' => 'free',
            'utm_content' => 'video_01',
            'utm_term' => null,
            'created_by' => 7,
            'created_by_name' => 'Man',
        ]);
        $trackingLink->setRelation('creator', (object) ['name' => 'Man']);

        $service = new CrmLeadService(
            $this->createMock(CrmLeadNotificationService::class),
            $this->createMock(CrmLeadConsentService::class)
        );
        $method = new ReflectionMethod($service, 'applyResolvedTrackingLink');
        $method->setAccessible(true);

        $attribution = $method->invoke($service, [
            'utm_source' => 'tampered',
            'utm_campaign' => 'tampered',
        ], $trackingLink);

        $this->assertSame('facebook', $attribution['utm_source']);
        $this->assertSame('organic_social', $attribution['utm_medium']);
        $this->assertSame('free', $attribution['utm_campaign']);
        $this->assertSame(15, $attribution['tracking_link_id']);
        $this->assertSame('trackabc123', $attribution['tracking_link_code']);
        $this->assertSame('Facebook Free Trial', $attribution['tracking_link_name']);
        $this->assertSame(7, $attribution['tracking_link_creator_id']);
        $this->assertSame('Man', $attribution['tracking_link_creator_name']);
        $this->assertSame('facebook', $attribution['first_touch_source']);
    }
}
