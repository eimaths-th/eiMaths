<?php

namespace Tests\Feature\Crm;

use App\Models\CrmLead;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketingConsentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $branch = Department::forceCreate([
            'name' => 'Consent Branch', 'code' => 'CONSENT',
            'status' => 1, 'enabled_modules' => ['crm'],
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('crm-leads-menu-view', 'web'));
        $this->actor = User::factory()->create([
            'department_id' => $branch->id, 'disable' => 0,
            'language_code' => 'en', 'password' => bcrypt('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    private function Lead(array $overrides = []): CrmLead
    {
        return CrmLead::forceCreate(array_replace([
            'branch_id' => $this->actor->department_id,
            'parent_name' => 'Consent Parent', 'phone' => '0891234567',
            'student_name' => 'Consent Student', 'student_grade' => 'P1',
            'created_from' => 'lead_registration_website', 'lead_status' => 'new_lead',
        ], $overrides));
    }

    #[Test]
    public function HistoricalLeadShowsNoSeparatePromotionEvidence(): void
    {
        $lead = $this->Lead();
        $this->actingAs($this->actor, 'web')->get(route('crm.leads.show', $lead))
            ->assertOk()->assertSee('Promotion choice')->assertSee('No separate evidence');
    }

    #[Test]
    public function ShowsTheLatestChoiceAndOnlyTenRecentEvents(): void
    {
        $lead = $this->Lead([
            'marketing_consent' => false,
            'marketing_consent_recorded_at' => '2026-09-24 09:00:00',
            'marketing_consent_context' => [
                'notice_version' => 'p1-3-v1', 'consent_version' => 'marketing-v1',
                'channels' => ['line', 'phone', 'email'], 'source' => 'website-free-trial',
            ],
        ]);
        for ($sequence = 1; $sequence <= 12; $sequence++) {
            $lead->ConsentEvents()->create([
                'marketing_consent' => $sequence % 2 === 0,
                'recorded_at' => sprintf('2026-09-24 09:%02d:00', $sequence),
                'context' => [
                    'notice_version' => 'p1-3-v1', 'consent_version' => 'marketing-v1',
                    'channels' => ['line', 'phone', 'email'], 'source' => 'website-free-trial',
                ],
            ]);
        }
        $response = $this->actingAs($this->actor, 'web')->get(route('crm.leads.show', $lead))
            ->assertOk()->assertSee('Declined')->assertSee('marketing-v1')
            ->assertSee('website-free-trial')->assertSee('LINE, phone, email');
        $this->assertSame(10, substr_count($response->getContent(), 'class="consent-event '));
        $this->assertStringContainsString('09:12:00', $response->getContent());
        $this->assertStringContainsString('09:11:00', $response->getContent());
        $this->assertStringNotContainsString('09:02:00', $response->getContent());
        $this->assertLessThan(
            strpos($response->getContent(), '09:11:00'),
            strpos($response->getContent(), '09:12:00')
        );
    }

    #[Test]
    public function OtherBranchAndUnauthorizedStaffCannotSeeTheEvidence(): void
    {
        $other_branch = Department::forceCreate([
            'name' => 'Other Branch', 'code' => 'OTHER',
            'status' => 1, 'enabled_modules' => ['crm'],
        ]);
        $other_lead = $this->Lead(['branch_id' => $other_branch->id, 'marketing_consent' => true]);
        $this->actingAs($this->actor, 'web')->get(route('crm.leads.show', $other_lead))->assertNotFound();

        $unauthorized = User::factory()->create([
            'department_id' => $this->actor->department_id, 'disable' => 0,
            'password' => bcrypt('secret'),
        ]);
        $this->actingAs($unauthorized, 'web')->get(route('crm.leads.show', $this->Lead()))->assertForbidden();
    }

    #[Test]
    public function ThaiPageShowsAnExplicitAcceptedChoice(): void
    {
        $lead = $this->Lead([
            'marketing_consent' => true,
            'marketing_consent_recorded_at' => '2026-09-24 09:00:00',
            'marketing_consent_context' => [
                'notice_version' => 'p1-3-v1', 'consent_version' => 'marketing-v1',
                'channels' => ['line', 'phone', 'email'], 'source' => 'website-free-trial',
            ],
        ]);
        $this->actor->update(['language_code' => 'th']);
        $this->actingAs($this->actor, 'web')->get(route('crm.leads.show', $lead))
            ->assertOk()->assertSee('ตัวเลือกการรับโปรโมชัน')
            ->assertSee('ยินยอม')->assertSee('โทรศัพท์');
    }
}
