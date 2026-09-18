<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CentreManagementLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Department $branch;

    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Centre Management HQ',
            'code' => 'CENTRE-MANAGEMENT-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $this->branch = Department::create([
            'name' => 'Ratchaphruek',
            'code' => 'RATCHA',
            'status' => 1,
            'enabled_modules' => null,
            'branch_type' => 'branch',
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        foreach ([
            'centre-manage', 'centre-list', 'centre-create', 'centre-edit', 'centre-delete',
            'department-list', 'department-create', 'department-edit', 'department-delete', 'all-centre',
        ] as $permission_name) {
            $role->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);

        $this->position = Position::create([
            'name' => 'Teacher',
            'department_id' => $this->branch->id,
            'status' => 1,
        ]);
    }

    public static function CentreLabels(): array
    {
        return [
            ['en', 'Centres', 'Centre Name', 'Centre Code', 'Status', 'Action', 'Add Centre', 'Edit Centre'],
            ['th', 'สาขา', 'ชื่อสาขา', 'รหัสสาขา', 'สถานะ', 'การดำเนินการ', 'เพิ่มสาขา', 'แก้ไขสาขา'],
        ];
    }

    public static function DepartmentLabels(): array
    {
        return [
            ['en', 'Departments', 'Department', 'Centre', 'Status', 'Action', 'Add Department', 'Edit Department'],
            ['th', 'แผนก', 'แผนก', 'สาขา', 'สถานะ', 'การดำเนินการ', 'เพิ่มแผนก', 'แก้ไขแผนก'],
        ];
    }

    public static function UpdateMessages(): array
    {
        return [
            ['en', 'Branch settings saved.', 'Department updated successfully.'],
            ['th', 'บันทึกการตั้งค่าสาขาแล้ว', 'แก้ไขแผนกสำเร็จ'],
        ];
    }

    #[Test]
    #[DataProvider('CentreLabels')]
    public function CentrePageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $name_label,
        string $code_label,
        string $status_label,
        string $action_label,
        string $create_heading,
        string $edit_heading,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('departments'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($name_label)
            ->assertSee($code_label)
            ->assertSee($status_label)
            ->assertSee($action_label)
            ->assertSee($create_heading)
            ->assertSee($edit_heading)
            ->assertDontSee('centre-management.');

        $ajax_response = $this->actingAs($this->actor, 'web')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('departments'));

        $ajax_response->assertOk();
        $action = (string) data_get($ajax_response->json(), 'data.0.action');
        $this->assertStringContainsString($locale === 'th' ? 'แก้ไข' : 'Edit', $action);
    }

    #[Test]
    #[DataProvider('DepartmentLabels')]
    public function DepartmentPageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $department_label,
        string $centre_label,
        string $status_label,
        string $action_label,
        string $create_heading,
        string $edit_heading,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('positions'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($department_label)
            ->assertSee($centre_label)
            ->assertSee($status_label)
            ->assertSee($action_label)
            ->assertSee($create_heading)
            ->assertSee($edit_heading)
            ->assertDontSee('centre-management.');

        $ajax_response = $this->actingAs($this->actor, 'web')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('positions'));

        $ajax_response->assertOk();
        $action = (string) data_get($ajax_response->json(), 'data.0.action');
        $this->assertStringContainsString($locale === 'th' ? 'แก้ไข' : 'Edit', $action);
        $this->assertStringContainsString($locale === 'th' ? 'ลบ' : 'Delete', $action);
    }

    #[Test]
    #[DataProvider('UpdateMessages')]
    public function CentreAndDepartmentUpdatesUseTheSelectedLocale(
        string $locale,
        string $centre_updated_message,
        string $department_updated_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $centre_response = $this->actingAs($this->actor, 'web')->putJson(
            route('departments.save', $this->branch),
            ['name' => 'Ratchaphruek Updated', 'code' => 'RATCHA2', 'status' => 1],
        );
        $centre_response->assertOk()->assertJsonPath('success', $centre_updated_message);

        $department_response = $this->actingAs($this->actor, 'web')->putJson(
            route('positions.save', $this->position),
            ['name' => 'Senior Teacher', 'department' => $this->branch->id, 'status' => 1],
        );
        $department_response->assertOk()->assertJsonPath('success', $department_updated_message);
    }
}
