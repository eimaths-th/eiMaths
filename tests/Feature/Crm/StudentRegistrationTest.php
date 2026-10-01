<?php

namespace Tests\Feature\Crm;

use App\Models\CrmLead;
use App\Services\CrmLeadNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.crm_integrations.token' => 'isolated-student-token']);
        Http::preventStrayRequests();
        Mail::fake();
        $this->app->instance(CrmLeadNotificationService::class, $this->createMock(CrmLeadNotificationService::class));
    }

    private function Register(string $student_name, string $student_grade, array $overrides = []): TestResponse
    {
        return $this->postJson('/api/n8n/marketing/leads', array_replace([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => $student_name,
            'student_grade' => $student_grade,
        ], $overrides), ['Authorization' => 'Bearer isolated-student-token']);
    }

    #[Test]
    public function RegisteringAnotherChildWithTheSameParentPhoneCreatesASeparateLead(): void
    {
        $first_lead_id = $this->Register('First Child', 'P1')->assertCreated()->json('lead_id');

        $second_response = $this->Register('Second Child', 'P1')
            ->assertCreated()
            ->assertJsonPath('action', 'created')
            ->assertJsonPath('lead.student_name', 'Second Child')
            ->assertJsonPath('lead.student_grade', 'P1');

        $this->assertNotSame($first_lead_id, $second_response->json('lead_id'));
        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $first_lead_id,
            'phone' => '0891234567',
            'student_name' => 'First Child',
            'student_grade' => 'P1',
        ]);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $second_response->json('lead_id'),
            'phone' => '0891234567',
            'student_name' => 'Second Child',
            'student_grade' => 'P1',
        ]);
    }

    #[Test]
    public function RetryingAnEarlierChildSelectsThatChildAfterASiblingWasRegistered(): void
    {
        $first_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'First Child',
            'student_grade' => 'P1',
            'created_at' => now()->subMinute(),
        ]);
        $second_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'Second Child',
            'student_grade' => 'P1',
            'created_at' => now(),
        ]);

        $this->Register('First Child', 'P1')->assertOk()
            ->assertJsonPath('action', 'merged')
            ->assertJsonPath('lead_id', $first_lead->id)
            ->assertJsonPath('lead.student_name', 'First Child');

        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', ['id' => $second_lead->id, 'student_name' => 'Second Child']);
    }

    #[Test]
    public function CompletingABlankNameLiffLeadUsesTheOriginalLead(): void
    {
        $pending_lead = CrmLead::forceCreate([
            'parent_name' => '',
            'phone' => '',
            'student_name' => '',
            'student_grade' => '',
            'line_user_id' => 'line-shared-parent',
            'created_from' => 'free_trial_liff_login',
            'lead_status' => 'line_login_pending',
        ]);

        $this->Register('First Child', 'P1', ['line_user_id' => 'line-shared-parent'])
            ->assertOk()
            ->assertJsonPath('action', 'merged')
            ->assertJsonPath('lead_id', $pending_lead->id)
            ->assertJsonPath('lead.student_name', 'First Child');

        $this->assertDatabaseCount('crm_leads', 1);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $pending_lead->id,
            'phone' => '0891234567',
            'student_name' => 'First Child',
        ]);
    }

    #[Test]
    public function RegisteringAnotherChildDoesNotAbsorbAChattingLineLead(): void
    {
        $line_registration = ['line_user_id' => 'line-shared-parent'];
        $first_lead_id = $this->Register('First Child', 'P1', $line_registration + [
            'lead_status' => 'line_chatting',
        ])->assertCreated()->json('lead_id');

        $second_response = $this->Register('Second Child', 'P1', $line_registration)
            ->assertCreated()
            ->assertJsonPath('action', 'created')
            ->assertJsonPath('lead.student_name', 'Second Child');

        $this->assertNotSame($first_lead_id, $second_response->json('lead_id'));
        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $first_lead_id,
            'student_name' => 'First Child',
            'lead_status' => 'line_chatting',
        ]);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $second_response->json('lead_id'),
            'student_name' => 'Second Child',
        ]);
    }

    #[Test]
    public function RetryingTheSecondLineChildMergesTheSecondLead(): void
    {
        $first_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'First Child',
            'student_grade' => 'P1',
            'line_user_id' => 'line-shared-parent',
            'lead_status' => 'line_chatting',
            'created_at' => now()->subMinute(),
        ]);
        $second_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'Second Child',
            'student_grade' => 'P1',
            'line_user_id' => 'line-shared-parent',
            'lead_status' => 'new_lead',
            'created_at' => now(),
        ]);

        $this->Register('Second Child', 'P1', ['line_user_id' => 'line-shared-parent'])
            ->assertOk()
            ->assertJsonPath('action', 'merged')
            ->assertJsonPath('lead_id', $second_lead->id)
            ->assertJsonPath('lead.student_name', 'Second Child');

        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', ['id' => $first_lead->id, 'student_name' => 'First Child']);
    }

    #[Test]
    public function NamedPendingLineLeadDoesNotAbsorbAnotherChild(): void
    {
        $first_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'First Child',
            'student_grade' => 'P1',
            'line_user_id' => 'line-shared-parent',
            'lead_status' => 'line_login_pending',
        ]);

        $second_response = $this->Register('Second Child', 'P1', [
            'line_user_id' => 'line-shared-parent',
        ])->assertCreated()->assertJsonPath('lead.student_name', 'Second Child');

        $this->assertNotSame($first_lead->id, $second_response->json('lead_id'));
        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $first_lead->id,
            'student_name' => 'First Child',
            'lead_status' => 'line_login_pending',
        ]);
    }

    #[Test]
    public function NamedPendingLineLeadMatchesItsChildInAMultiStudentSubmission(): void
    {
        $first_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'First Child',
            'student_grade' => 'P1',
            'line_user_id' => 'line-shared-parent',
            'lead_status' => 'line_login_pending',
        ]);

        $response = $this->postJson('/api/n8n/marketing/leads', [
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'line_user_id' => 'line-shared-parent',
            'students' => [
                ['student_name' => 'Second Child', 'student_grade' => 'P1'],
                ['student_name' => 'First Child', 'student_grade' => 'P1'],
            ],
        ], ['Authorization' => 'Bearer isolated-student-token'])
            ->assertCreated()
            ->assertJsonPath('leads.0.student_name', 'Second Child')
            ->assertJsonPath('leads.1.id', $first_lead->id)
            ->assertJsonPath('leads.1.student_name', 'First Child');

        $this->assertNotSame($first_lead->id, $response->json('leads.0.id'));
        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', ['id' => $first_lead->id, 'student_name' => 'First Child']);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $response->json('leads.0.id'),
            'student_name' => 'Second Child',
        ]);
    }

    #[Test]
    public function ExplicitPendingLeadEditCannotBeReusedForAnotherChildInTheSameSubmission(): void
    {
        $pending_lead = CrmLead::forceCreate([
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'student_name' => 'Original Child Name',
            'student_grade' => 'P1',
            'line_user_id' => 'line-shared-parent',
            'lead_status' => 'line_login_pending',
        ]);

        $response = $this->postJson('/api/n8n/marketing/leads', [
            'parent_name' => 'Shared Parent',
            'phone' => '0891234567',
            'line_user_id' => 'line-shared-parent',
            'students' => [
                ['lead_id' => $pending_lead->id, 'student_name' => 'Edited Child Name', 'student_grade' => 'P1'],
                ['student_name' => 'Original Child Name', 'student_grade' => 'P1'],
            ],
        ], ['Authorization' => 'Bearer isolated-student-token'])
            ->assertCreated()
            ->assertJsonPath('leads.0.id', $pending_lead->id)
            ->assertJsonPath('leads.0.student_name', 'Edited Child Name')
            ->assertJsonPath('leads.1.student_name', 'Original Child Name');

        $this->assertNotSame($pending_lead->id, $response->json('leads.1.id'));
        $this->assertDatabaseCount('crm_leads', 2);
        $this->assertDatabaseHas('crm_leads', ['id' => $pending_lead->id, 'student_name' => 'Edited Child Name']);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $response->json('leads.1.id'),
            'student_name' => 'Original Child Name',
        ]);
    }
}
