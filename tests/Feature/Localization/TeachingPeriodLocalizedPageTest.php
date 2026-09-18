<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\TeachingPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeachingPeriodLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $hq = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Teaching Period HQ',
            'code' => 'TEACHING-PERIOD-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $this->department = Department::forceCreate([
            'id' => 2,
            'name' => 'Ratchaphruek',
            'code' => 'RAT',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('SystemAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('course-manage', 'web'));

        $this->actor = User::factory()->create([
            'department_id' => $hq->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    public static function LocalizedLabels(): array
    {
        return [
            [
                'en', 'Teaching Period Management', 'Teaching Period Find', 'Department', 'Day',
                'Teaching Period', 'Actions', 'Select department', 'Select day', 'Find', 'Saturday',
            ],
            [
                'th', 'จัดการช่วงเวลาสอน', 'ค้นหาช่วงเวลาสอน', 'แผนก', 'วัน',
                'ช่วงเวลาสอน', 'การทำงาน', 'เลือกแผนก', 'เลือกวัน', 'ค้นหา', 'วันเสาร์',
            ],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function TeachingPeriodPageRendersTheSelectedLocale(
        string $locale,
        string $page_heading,
        string $find_heading,
        string $department_label,
        string $day_label,
        string $period_label,
        string $actions_label,
        string $select_department,
        string $select_day,
        string $find_label,
        string $day_value
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreateTeachingPeriod();

        $response = $this->actingAs($this->actor, 'web')->get(route('TeachingPeriod.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($page_heading)
            ->assertSee($find_heading)
            ->assertSee($department_label)
            ->assertSee($day_label)
            ->assertSee($period_label)
            ->assertSee($actions_label)
            ->assertSee($select_department)
            ->assertSee($select_day)
            ->assertSee($find_label)
            ->assertSee($day_value)
            ->assertDontSee('teaching_period.');
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function TeachingPeriodFindResponseUsesTheSelectedLocale(
        string $locale,
        string $page_heading,
        string $find_heading,
        string $department_label,
        string $day_label,
        string $period_label,
        string $actions_label,
        string $select_department,
        string $select_day,
        string $find_label,
        string $day_value
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $period = $this->CreateTeachingPeriod();

        $response = $this->actingAs($this->actor, 'web')->getJson(route(
            'TeachingPeriod.find',
            [$this->department->id, $period->day]
        ));

        $response->assertOk();
        $this->assertStringContainsString($day_value, (string) $response->json('html'));
    }

    private function CreateTeachingPeriod(): TeachingPeriod
    {
        return TeachingPeriod::create([
            'department_id' => $this->department->id,
            'day' => 'Saturday',
            'period' => '09:00-10:00',
        ]);
    }
}
