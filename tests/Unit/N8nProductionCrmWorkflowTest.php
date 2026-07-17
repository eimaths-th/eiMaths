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

    public function test_line_loading_runs_before_ai_validation_without_blocking_the_reply(): void
    {
        $loading = $this->node('Show LINE Loading');
        $restore = $this->node('Restore AI Validation Request');

        $this->assertSame('n8n-nodes-base.httpRequest', $loading['type']);
        $this->assertSame('POST', $loading['parameters']['method']);
        $this->assertSame('https://api.line.me/v2/bot/chat/loading/start', $loading['parameters']['url']);
        $this->assertStringContainsString("$('Normalize LINE Event').item.json.lineUserId", $loading['parameters']['body']);
        $this->assertStringContainsString('loadingSeconds: 60', $loading['parameters']['body']);
        $this->assertTrue($loading['continueOnFail']);
        $this->assertTrue($loading['retryOnFail']);
        $this->assertSame(2, $loading['maxTries']);
        $this->assertSame('n8n-nodes-base.code', $restore['type']);

        $aiBranch = array_column($this->workflow['connections']['Should Use AI Validation?']['main'][0], 'node');
        $loadingTargets = array_column($this->workflow['connections']['Show LINE Loading']['main'][0], 'node');
        $restoreTargets = array_column($this->workflow['connections']['Restore AI Validation Request']['main'][0], 'node');

        $this->assertSame(['Show LINE Loading'], $aiBranch);
        $this->assertSame(['Restore AI Validation Request'], $loadingTargets);
        $this->assertSame(['AI Validation Agent'], $restoreTargets);
    }

    public function test_completed_conversation_is_silent_except_for_exact_restart_command(): void
    {
        $completionGuard = $this->node('Guard Completed Conversation');
        $silenceGuard = $this->node('Enforce Post-Completion Silence');

        $this->assertStringContainsString('registration_completed', $completionGuard['parameters']['jsCode']);
        $this->assertStringContainsString('exact_restart_command', $completionGuard['parameters']['jsCode']);
        $this->assertStringContainsString('directDecision: null', $completionGuard['parameters']['jsCode']);
        $this->assertStringContainsString('postCompletionSilence: true', $completionGuard['parameters']['jsCode']);
        $this->assertStringContainsString("action: 'silent_handover'", $silenceGuard['parameters']['jsCode']);

        $detectTargets = array_column($this->workflow['connections']['Detect Global Intent']['main'][0], 'node');
        $completionGuardTargets = array_column($this->workflow['connections']['Guard Completed Conversation']['main'][0], 'node');
        $consentTargets = array_column($this->workflow['connections']['Consent Gate']['main'][0], 'node');
        $silenceGuardTargets = array_column($this->workflow['connections']['Enforce Post-Completion Silence']['main'][0], 'node');

        $this->assertSame(['Guard Completed Conversation'], $detectTargets);
        $this->assertSame(['Has Immediate Decision?'], $completionGuardTargets);
        $this->assertSame(['Enforce Post-Completion Silence'], $consentTargets);
        $this->assertSame(['Needs Field Validation?'], $silenceGuardTargets);
    }

    public function test_follow_event_resets_n8n_static_conversation_context(): void
    {
        $reset = $this->node('Reset Conversation on Follow');

        $this->assertStringContainsString("item.eventType !== 'follow'", $reset['parameters']['jsCode']);
        $this->assertStringContainsString("store.conversations[item.lineUserId] = conversation", $reset['parameters']['jsCode']);
        $this->assertStringContainsString("state: 'waiting_consent'", $reset['parameters']['jsCode']);
        $this->assertStringContainsString('existingAnswers: {}', $reset['parameters']['jsCode']);

        $loadTargets = array_column($this->workflow['connections']['Load Conversation Context']['main'][0], 'node');
        $resetTargets = array_column($this->workflow['connections']['Reset Conversation on Follow']['main'][0], 'node');

        $this->assertSame(['Reset Conversation on Follow'], $loadTargets);
        $this->assertSame(['Detect Global Intent'], $resetTargets);
    }

    public function test_short_consent_refusal_is_normalized_to_decline_without_repeating_prompt(): void
    {
        $normalizer = $this->node('Normalize Consent Refusal');

        $this->assertStringContainsString("item.conversationState === 'waiting_consent'", $normalizer['parameters']['jsCode']);
        $this->assertStringContainsString("normalizedText: 'ไม่ยินยอม'", $normalizer['parameters']['jsCode']);
        $this->assertStringContainsString('ไม่ค่ะ', $normalizer['parameters']['jsCode']);
        $this->assertStringContainsString('ไม่สะดวก', $normalizer['parameters']['jsCode']);

        $hasImmediateFalseTargets = array_column($this->workflow['connections']['Has Immediate Decision?']['main'][1], 'node');
        $normalizerTargets = array_column($this->workflow['connections']['Normalize Consent Refusal']['main'][0], 'node');

        $this->assertSame(['Normalize Consent Refusal'], $hasImmediateFalseTargets);
        $this->assertSame(['Consent Gate'], $normalizerTargets);
    }

    public function test_validation_policy_is_rule_first_and_ai_only_for_ambiguous_fields(): void
    {
        $prepare = $this->node('Prepare AI Validation Request');
        $parse = $this->node('Parse AI Validation');

        $this->assertStringContainsString("hybrid_validation.status === 'needs_ai'", $prepare['parameters']['jsCode']);
        $this->assertStringContainsString('Rule validator already made a deterministic decision. AI is skipped.', $prepare['parameters']['jsCode']);
        $this->assertStringContainsString('confidence >= 0.75', $parse['parameters']['jsCode']);

        $aiOnlyBranch = array_column($this->workflow['connections']['Should Use AI Validation?']['main'][0], 'node');
        $ruleBranch = array_column($this->workflow['connections']['Should Use AI Validation?']['main'][1], 'node');
        $consentBranch = array_column($this->workflow['connections']['Needs Field Validation?']['main'][1], 'node');

        $this->assertSame(['Show LINE Loading'], $aiOnlyBranch);
        $this->assertSame(['Apply Conversation Decision'], $ruleBranch);
        $this->assertSame(['Apply Conversation Decision'], $consentBranch);
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
