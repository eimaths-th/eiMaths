<?php

namespace Tests\Feature\Crm;

use App\Models\CrmLead;
use App\Models\Department;
use App\Models\TrackingLink;
use App\Models\User;
use App\Services\CrmLeadNotificationService;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PreferredBranchRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        config(['services.crm_integrations.token' => 'isolated-preference-token']);
        Http::preventStrayRequests();
        Mail::fake();
        $this->app->instance(CrmLeadNotificationService::class, $this->createMock(CrmLeadNotificationService::class));
    }

    private function Payload(): array
    {
        return [
            'parent_name' => 'Preference Test Parent',
            'phone' => '0891234567',
            'student_name' => 'Preference Test Student',
            'student_grade' => 'P1',
            'created_from' => 'lead_registration_website',
        ];
    }

    private function Register(array $overrides = []): TestResponse
    {
        return $this->postJson('/api/n8n/marketing/leads', array_replace($this->Payload(), $overrides), [
            'Authorization' => 'Bearer isolated-preference-token',
        ]);
    }

    public static function BranchCodes(): array
    {
        return [['ratchapruek'], ['bangkae']];
    }

    #[Test]
    #[DataProvider('BranchCodes')]
    public function SavesThePreferenceWithoutAssigningTheLead(string $branch_code): void
    {
        $response = $this->Register(['preferred_branch' => $branch_code])
            ->assertCreated()->assertJsonPath('success', true)
            ->assertJsonPath('lead.preferred_branch', $branch_code);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $response->json('lead_id'), 'preferred_branch' => $branch_code,
            'branch_id' => null, 'assigned_staff_id' => null,
        ]);
    }

    #[Test]
    public function OmittedOrNullPreferenceRemainsCompatible(): void
    {
        foreach ([[], ['preferred_branch' => null, 'phone' => '0891234568']] as $overrides) {
            $response = $this->Register($overrides)->assertCreated()
                ->assertJsonStructure(['lead' => ['preferred_branch']])
                ->assertJsonPath('lead.preferred_branch', null);
            $this->assertNull(CrmLead::findOrFail($response->json('lead_id'))->preferred_branch);
        }
        $this->assertTrue(Schema::hasColumn('crm_leads', 'preferred_branch'));
    }

    public static function InvalidPreferences(): array
    {
        return [['unknown'], [123], [['bangkae']]];
    }

    #[Test]
    #[DataProvider('InvalidPreferences')]
    public function RejectsUnsupportedPreferences(mixed $preference): void
    {
        $this->Register(['preferred_branch' => $preference])->assertUnprocessable()
            ->assertJsonValidationErrors(['preferred_branch']);
        $this->assertDatabaseCount('crm_leads', 0);
    }

    #[Test]
    public function AppliesTheTopLevelPreferenceToEachStudent(): void
    {
        $response = $this->Register([
            'preferred_branch' => 'bangkae',
            'students' => [
                ['student_name' => 'First Student', 'student_grade' => 'K3'],
                ['student_name' => 'Second Student', 'student_grade' => 'P6'],
            ],
        ])->assertCreated()->assertJsonCount(2, 'leads');
        foreach ($response->json('leads') as $lead) {
            $this->assertArrayHasKey('preferred_branch', $lead);
            $this->assertSame('bangkae', $lead['preferred_branch']);
            $this->assertDatabaseHas('crm_leads', [
                'id' => $lead['id'], 'preferred_branch' => 'bangkae', 'branch_id' => null,
            ]);
        }
    }

    #[Test]
    public function StillRequiresIntegrationAuthentication(): void
    {
        $this->postJson('/api/n8n/marketing/leads', $this->Payload() + ['preferred_branch' => 'bangkae'])
            ->assertUnauthorized();
        $this->assertDatabaseCount('crm_leads', 0);
    }
    #[Test]
    public function LatestExplicitPreferenceWinsButOmissionAndNullPreserveIt(): void
    {
        $lead_id = $this->Register(['preferred_branch' => 'ratchapruek'])->assertCreated()->json('lead_id');
        CrmLead::findOrFail($lead_id)->update(['lead_status' => 'enrolled']);

        $this->Register(['preferred_branch' => 'bangkae'])->assertOk()
            ->assertJsonPath('lead_id', $lead_id)->assertJsonPath('lead.preferred_branch', 'bangkae');
        foreach ([[], ['preferred_branch' => null]] as $overrides) {
            $this->Register($overrides)->assertOk()->assertJsonPath('lead.preferred_branch', 'bangkae');
        }
        $this->assertDatabaseCount('crm_leads', 1);
        $this->assertDatabaseHas('crm_leads', [
            'id' => $lead_id, 'preferred_branch' => 'bangkae',
            'branch_id' => null, 'assigned_staff_id' => null, 'lead_status' => 'enrolled',
        ]);
    }

    #[Test]
    public function ExplicitStudentIdUsesTheSamePreferenceMergeRule(): void
    {
        $lead_id = $this->Register(['preferred_branch' => 'ratchapruek'])->assertCreated()->json('lead_id');
        $this->Register([
            'preferred_branch' => 'bangkae',
            'students' => [['lead_id' => $lead_id, 'student_name' => 'Preference Test Student', 'student_grade' => 'P1']],
        ])->assertOk()->assertJsonPath('leads.0.id', $lead_id)->assertJsonPath('leads.0.preferred_branch', 'bangkae');
        $this->assertDatabaseCount('crm_leads', 1);
        $this->assertDatabaseHas('crm_leads', ['id' => $lead_id, 'preferred_branch' => 'bangkae']);
    }

    #[Test]
    public function PreferenceDoesNotReassignATrackedLeadOrExpandBranchAccess(): void
    {
        Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        $assigned_branch = Department::create(['name' => 'Ratchapruek', 'code' => 'RP', 'status' => 1, 'enabled_modules' => ['crm']]);
        $preferred_branch = Department::create(['name' => 'Bangkae', 'code' => 'BK', 'status' => 1, 'enabled_modules' => ['crm']]);
        $assigned_staff = User::factory()->create(['department_id' => $assigned_branch->id, 'disable' => 0, 'password' => bcrypt('password-123')]);
        $other_staff = User::factory()->create(['department_id' => $preferred_branch->id, 'disable' => 0, 'password' => bcrypt('password-123')]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $assigned_staff->assignRole($role);
        $other_staff->assignRole($role);
        $tracking_link = TrackingLink::create([
            'branch_id' => $assigned_branch->id,
            'tracking_code' => 'preference_test_link',
            'name' => 'Isolated branch campaign',
            'base_url' => 'https://eimaths-th.com/free-trial',
            'generated_url' => 'https://eimaths-th.com/free-trial?tracking_code=preference_test_link',
            'utm_source' => 'facebook', 'utm_medium' => 'social', 'utm_campaign' => 'branch-campaign',
            'created_by' => $assigned_staff->id,
        ]);
        $lead_id = $this->Register([
            'tracking_code' => 'preference_test_link', 'preferred_branch' => 'ratchapruek',
        ])->assertCreated()->json('lead_id');
        CrmLead::findOrFail($lead_id)->update(['assigned_staff_id' => $assigned_staff->id]);
        $this->Register([
            'tracking_code' => 'preference_test_link', 'preferred_branch' => 'bangkae', 'utm_source' => 'tampered',
        ])->assertOk()->assertJsonPath('lead_id', $lead_id)->assertJsonPath('lead.preferred_branch', 'bangkae');

        $this->assertDatabaseHas('crm_leads', [
            'id' => $lead_id, 'preferred_branch' => 'bangkae', 'branch_id' => $assigned_branch->id,
            'assigned_staff_id' => $assigned_staff->id, 'tracking_link_id' => $tracking_link->id,
            'utm_source' => 'facebook', 'utm_campaign' => 'branch-campaign',
        ]);
        $branch_context = app(BranchContext::class);
        $this->assertTrue($branch_context->runAs($assigned_staff, fn (): bool => CrmLead::whereKey($lead_id)->exists()));
        $this->assertFalse($branch_context->runAs($other_staff, fn (): bool => CrmLead::whereKey($lead_id)->exists()));
    }
}
