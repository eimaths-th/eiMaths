<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class N8nProductionCrmWorkflowTest extends TestCase
{
    private array $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2).'/n8n/eimaths-new-customer-line-oa-lead-registration-marketing-notify.json';
        $json = file_get_contents($path);

        $this->assertIsString($json);
        $this->assertStringNotContainsString('test-web.eimaths-th.com', $json);

        $this->workflow = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_crm_write_is_guarded_and_retried_against_production(): void
    {
        $guard = $this->node('Enforce Production CRM Target');
        $createLead = $this->node('Create or Merge CRM Lead');

        $this->assertStringContainsString('https://app.eimaths-th.com', $guard['parameters']['jsCode']);
        $this->assertStringContainsString('Blocked non-production CRM target', $guard['parameters']['jsCode']);
        $this->assertStringContainsString('config.crmBaseUrl', $createLead['parameters']['url']);
        $this->assertTrue($createLead['retryOnFail']);
        $this->assertSame(3, $createLead['maxTries']);
        $this->assertSame(5000, $createLead['waitBetweenTries']);

        $normalizeTargets = array_column($this->workflow['connections']['Normalize LINE Event']['main'][0], 'node');
        $this->assertSame(['Enforce Production CRM Target'], $normalizeTargets);

        $guardTargets = array_column($this->workflow['connections']['Enforce Production CRM Target']['main'][0], 'node');
        $this->assertEqualsCanonicalizing(['Get LINE Profile', 'Merge Event and Profile'], $guardTargets);
    }

    public function test_marketing_notification_forces_the_production_crm_origin(): void
    {
        $urlGuard = $this->node('Enforce Production CRM URL');
        $notify = $this->node('Notify Marketing Team');

        $this->assertStringContainsString('https://app.eimaths-th.com', $urlGuard['parameters']['jsCode']);
        $this->assertStringContainsString("$('Enforce Production CRM URL')", $notify['parameters']['body']);

        $buildTargets = array_column($this->workflow['connections']['Build CRM Notification']['main'][0], 'node');
        $this->assertSame(['Enforce Production CRM URL'], $buildTargets);

        $urlGuardTargets = array_column($this->workflow['connections']['Enforce Production CRM URL']['main'][0], 'node');
        $this->assertSame(['Notify Marketing Team'], $urlGuardTargets);
    }

    private function node(string $name): array
    {
        foreach ($this->workflow['nodes'] as $node) {
            if ($node['name'] === $name) {
                return $node;
            }
        }

        $this->fail("Missing n8n node: {$name}");
    }
}
