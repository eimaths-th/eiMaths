<?php

namespace Tests\Unit;

use App\Models\CrmLead;
use App\Services\CrmLeadNotificationService;
use App\Services\CrmLeadService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CrmLeadAttributionTest extends TestCase
{
    public function test_line_chat_fallback_does_not_replace_existing_marketing_source(): void
    {
        $lead = new CrmLead([
            'utm_source' => 'facebook',
            'utm_medium' => 'organic_social',
            'utm_campaign' => 'unknown',
            'first_touch_source' => 'facebook',
            'last_touch_source' => 'facebook',
        ]);

        $incoming = [
            'utm_source' => 'line',
            'utm_medium' => 'new_customer_oa_chat',
            'utm_campaign' => 'unknown',
            'first_touch_source' => 'line',
            'last_touch_source' => 'new_customer_line_oa',
        ];

        $service = new CrmLeadService($this->createMock(CrmLeadNotificationService::class));
        $method = new ReflectionMethod($service, 'mergeLeadData');
        $method->setAccessible(true);

        $merged = $method->invoke($service, $lead, $incoming);

        $this->assertArrayNotHasKey('utm_source', $merged);
        $this->assertArrayNotHasKey('utm_medium', $merged);
        $this->assertArrayNotHasKey('utm_campaign', $merged);
        $this->assertSame('facebook', $merged['first_touch_source']);
        $this->assertSame('new_customer_line_oa', $merged['last_touch_source']);
    }

    public function test_line_source_is_used_when_there_is_no_previous_marketing_attribution(): void
    {
        $lead = new CrmLead;
        $incoming = [
            'utm_source' => 'line',
            'utm_medium' => 'new_customer_oa_chat',
            'utm_campaign' => 'unknown',
            'first_touch_source' => 'line',
            'last_touch_source' => 'new_customer_line_oa',
        ];

        $service = new CrmLeadService($this->createMock(CrmLeadNotificationService::class));
        $method = new ReflectionMethod($service, 'mergeLeadData');
        $method->setAccessible(true);

        $merged = $method->invoke($service, $lead, $incoming);

        $this->assertSame('line', $merged['utm_source']);
        $this->assertSame('new_customer_oa_chat', $merged['utm_medium']);
        $this->assertSame('unknown', $merged['utm_campaign']);
        $this->assertSame('line', $merged['first_touch_source']);
        $this->assertSame('new_customer_line_oa', $merged['last_touch_source']);
    }
}
