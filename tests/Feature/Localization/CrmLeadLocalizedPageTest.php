<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\CrmLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrmLeadLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'name' => 'CRM Test Branch',
            'code' => 'CRM-TEST',
            'status' => 1,
            'enabled_modules' => ['crm'],
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('crm-leads-menu-view', 'web'));

        $this->actor = User::factory()->create([
            'department_id' => $branch->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    public static function LocalizedLabels(): array
    {
        return [
            ['en', 'CRM Leads', 'Search Keyword', 'All statuses'],
            ['th', 'รายชื่อลูกค้า CRM', 'ค้นหาคำค้น', 'ทุกสถานะ'],
        ];
    }

    public static function LocalizedDetailLabels(): array
    {
        return [
            ['en', 'Lead Detail #', 'Lead Information', 'Notes / Contact History', 'Add contact note...'],
            ['th', 'รายละเอียด Lead #', 'ข้อมูล Lead', 'หมายเหตุ / ประวัติการติดต่อ', 'เพิ่มบันทึกการติดต่อ...'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function LeadsListRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $search_label,
        string $status_option
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('crm.leads.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($search_label)
            ->assertSee($status_option)
            ->assertDontSee('crm.filters.');
    }

    #[Test]
    #[DataProvider('LocalizedDetailLabels')]
    public function LeadDetailRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $information_heading,
        string $notes_heading,
        string $note_placeholder
    ): void {
        $lead = CrmLead::forceCreate([
            'branch_id' => $this->actor->department_id,
            'parent_name' => 'Test Parent',
            'phone' => '0800000000',
            'student_name' => 'Test Student',
            'student_grade' => 'P1',
            'created_from' => 'lead_registration_website',
            'lead_status' => 'new_lead',
        ]);
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('crm.leads.show', $lead));

        $response->assertOk()
            ->assertSee($heading)
            ->assertSee($information_heading)
            ->assertSee($notes_heading)
            ->assertSee($note_placeholder)
            ->assertDontSee('crm.lead_detail.');
    }
}
