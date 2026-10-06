<?php

namespace Tests\Feature\Crm;

use App\Models\CrmLead;
use App\Models\CrmLeadConsentEvent;
use App\Services\CrmLeadNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Tests\TestCase;

class MarketingConsentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        config(['services.crm_integrations.token' => 'isolated-consent-token']);
        Http::preventStrayRequests();
        Mail::fake();
        $this->app->instance(CrmLeadNotificationService::class, $this->createMock(CrmLeadNotificationService::class));
    }

    private function Context(): array
    {
        return [
            'notice_version' => 'p1-3-v1',
            'consent_version' => 'marketing-v1',
            'channels' => ['line', 'phone', 'email'],
            'source' => 'website-free-trial',
        ];
    }

    private function Register(array $overrides = []): TestResponse
    {
        return $this->postJson('/api/n8n/marketing/leads', array_replace([
            'parent_name' => 'Consent Test Parent',
            'phone' => '0891234567',
            'student_name' => 'Consent Test Student',
            'student_grade' => 'P1',
            'created_from' => 'lead_registration_website',
        ], $overrides), ['Authorization' => 'Bearer isolated-consent-token']);
    }

    #[Test]
    public function RecordsAnExplicitPromotionChoiceInTheApiResponse(): void
    {
        $this->travelTo('2026-09-24 09:00:00');
        $response = $this->Register([
            'marketing_consent' => true,
            'marketing_consent_context' => $this->Context(),
            'marketing_consent_recorded_at' => '2000-01-01 00:00:00',
        ])
            ->assertCreated()
            ->assertJsonPath('lead.marketing_consent', true)
            ->assertJsonPath('lead.marketing_consent_context', $this->Context());

        $lead = CrmLead::findOrFail($response->json('lead_id'));
        $this->assertTrue($lead->marketing_consent);
        $this->assertSame('2026-09-24 09:00:00', $lead->marketing_consent_recorded_at->format('Y-m-d H:i:s'));
        $this->assertSame($this->Context(), $lead->marketing_consent_context);
        $this->assertDatabaseHas('crm_lead_consent_events', [
            'lead_id' => $lead->id, 'marketing_consent' => true, 'recorded_at' => '2026-09-24 09:00:00',
        ]);
    }

    #[Test]
    public function ExplicitDeclineReplacesAcceptanceAndOmissionPreservesIt(): void
    {
        $lead_id = $this->Register(['marketing_consent' => true, 'marketing_consent_context' => $this->Context()])
            ->assertCreated()->json('lead_id');
        $this->travel(1)->hour();
        $declined = $this->Register(['marketing_consent' => false, 'marketing_consent_context' => $this->Context()])
            ->assertOk()->assertJsonPath('lead_id', $lead_id)->assertJsonPath('lead.marketing_consent', false);
        $recorded_at = $declined->json('lead.marketing_consent_recorded_at');
        $this->travel(1)->hour();
        $this->Register()->assertOk()->assertJsonPath('lead_id', $lead_id)
            ->assertJsonPath('lead.marketing_consent', false)
            ->assertJsonPath('lead.marketing_consent_recorded_at', $recorded_at);

        $events = CrmLeadConsentEvent::where('lead_id', $lead_id)->orderBy('id')->get();
        $this->assertCount(2, $events);
        $this->assertTrue($events[0]->marketing_consent);
        $this->assertFalse($events[1]->marketing_consent);
    }

    #[Test]
    public function LegacyRegistrationHasUnknownConsent(): void
    {
        $response = $this->Register()->assertCreated()
            ->assertJsonPath('lead.marketing_consent', null)
            ->assertJsonPath('lead.marketing_consent_context', null);
        $this->assertNull(CrmLead::findOrFail($response->json('lead_id'))->marketing_consent);
        $this->assertDatabaseCount('crm_lead_consent_events', 0);
    }

    #[Test]
    public function RejectsMalformedOrIncompleteEvidence(): void
    {
        $cases = [
            [['marketing_consent' => null], 'marketing_consent'],
            [['marketing_consent' => 'yes', 'marketing_consent_context' => $this->Context()], 'marketing_consent'],
            [['marketing_consent' => true], 'marketing_consent_context'],
            [['marketing_consent_context' => $this->Context()], 'marketing_consent_context'],
            [['marketing_consent' => false, 'marketing_consent_context' => array_replace($this->Context(), ['notice_version' => 'wrong'])], 'marketing_consent_context.notice_version'],
            [['marketing_consent' => false, 'marketing_consent_context' => array_replace($this->Context(), ['channels' => ['email', 'phone', 'line']])], 'marketing_consent_context.channels.0'],
        ];
        foreach ($cases as [$input, $error_key]) {
            $this->Register($input)->assertUnprocessable()->assertJsonValidationErrors([$error_key]);
        }
        $this->assertDatabaseCount('crm_leads', 0);
    }

    #[Test]
    public function AppliesTheChoiceToEachStudentAndAnExplicitLeadId(): void
    {
        $response = $this->Register([
            'marketing_consent' => true,
            'marketing_consent_context' => $this->Context(),
            'students' => [
                ['student_name' => 'First Student', 'student_grade' => 'P1'],
                ['student_name' => 'Second Student', 'student_grade' => 'P2'],
            ],
        ])->assertCreated()->assertJsonCount(2, 'leads');
        foreach ($response->json('leads') as $lead) {
            $this->assertTrue($lead['marketing_consent']);
            $this->assertSame($this->Context(), $lead['marketing_consent_context']);
            $this->assertDatabaseHas('crm_lead_consent_events', ['lead_id' => $lead['id'], 'marketing_consent' => true]);
        }
        $first_id = $response->json('leads.0.id');
        $this->Register([
            'marketing_consent' => false,
            'marketing_consent_context' => $this->Context(),
            'students' => [['lead_id' => $first_id, 'student_name' => 'First Student', 'student_grade' => 'P1']],
        ])->assertOk()->assertJsonPath('leads.0.id', $first_id)->assertJsonPath('leads.0.marketing_consent', false);
        $this->assertSame(2, CrmLeadConsentEvent::where('lead_id', $first_id)->count());
    }

    #[Test]
    public function UpdatesAPendingLineLeadWithoutInferringConsentFromLineLogin(): void
    {
        $pending_id = CrmLead::forceCreate([
            'parent_name' => '', 'phone' => '',
            'student_name' => '', 'student_grade' => '',
            'line_user_id' => 'line-consent-test',
            'created_from' => 'free_trial_liff_login', 'lead_status' => 'line_login_pending',
        ])->id;
        $this->postJson('/api/line/free-trial/liff-lead', [
            'line_user_id' => 'line-consent-test',
            'line_display_name' => 'LINE Parent',
            'utm_source' => 'website',
            'utm_medium' => 'line_login_button',
            'utm_campaign' => 'test-campaign',
            'marketing_consent' => true,
        ], ['Authorization' => 'Bearer isolated-consent-token'])->assertOk()->assertJsonPath('lead_id', $pending_id);
        $this->assertNull(CrmLead::findOrFail($pending_id)->marketing_consent);
        $this->assertDatabaseCount('crm_lead_consent_events', 0);

        $this->Register([
            'line_user_id' => 'line-consent-test',
            'marketing_consent' => false,
            'marketing_consent_context' => $this->Context(),
            'students' => [['student_name' => 'Consent Test Student', 'student_grade' => 'P1']],
        ])->assertOk()->assertJsonPath('leads.0.id', $pending_id)
            ->assertJsonPath('leads.0.marketing_consent', false);
        $this->assertDatabaseHas('crm_lead_consent_events', [
            'lead_id' => $pending_id, 'marketing_consent' => false,
        ]);
    }

    #[Test]
    public function EnrolledLeadKeepsItsStatusWhileItsChoiceChanges(): void
    {
        $lead_id = $this->Register([
            'marketing_consent' => true,
            'marketing_consent_context' => $this->Context(),
        ])->assertCreated()->json('lead_id');
        CrmLead::findOrFail($lead_id)->update(['lead_status' => 'enrolled']);

        $this->Register([
            'marketing_consent' => false,
            'marketing_consent_context' => $this->Context(),
        ])->assertOk()->assertJsonPath('lead_id', $lead_id)
            ->assertJsonPath('lead.marketing_consent', false)
            ->assertJsonPath('lead.lead_status', 'enrolled');
        $this->assertSame(2, CrmLeadConsentEvent::where('lead_id', $lead_id)->count());
    }

    #[Test]
    public function EventFailureRollsBackTheLeadUpdate(): void
    {
        $lead_id = $this->Register()->assertCreated()->json('lead_id');
        CrmLeadConsentEvent::creating(function (): void {
            throw new RuntimeException('simulated event storage failure');
        });

        $this->Register(['marketing_consent' => true, 'marketing_consent_context' => $this->Context()])
            ->assertInternalServerError();

        $this->assertNull(CrmLead::findOrFail($lead_id)->marketing_consent);
        $this->assertDatabaseCount('crm_lead_consent_events', 0);
    }

    #[Test]
    public function UnauthenticatedRegistrationCannotRecordConsent(): void
    {
        $this->postJson('/api/n8n/marketing/leads', [
            'parent_name' => 'Unauthenticated', 'phone' => '0891234567',
            'student_name' => 'Student', 'student_grade' => 'P1',
            'marketing_consent' => true, 'marketing_consent_context' => $this->Context(),
        ])->assertUnauthorized();
        $this->assertDatabaseCount('crm_leads', 0);
    }
}
