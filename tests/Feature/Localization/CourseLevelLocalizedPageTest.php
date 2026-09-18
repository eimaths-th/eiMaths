<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\CourseSetting\Entities\CourseLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseLevelLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $hq = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Course Level Test HQ',
            'code' => 'COURSE-LEVEL-TEST-HQ',
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
            ['en', 'Course Levels', 'Add New Level', 'Course Level List', 'Name', 'Status', 'Action', 'Save'],
            ['th', 'ระดับคอร์ส', 'เพิ่มระดับคอร์ส', 'รายการระดับคอร์ส', 'ชื่อ', 'สถานะ', 'การทำงาน', 'บันทึก'],
        ];
    }

    public static function LocalizedActionMessages(): array
    {
        return [
            ['en', 'Course level added successfully.', 'Course level updated successfully.', 'Course level deleted successfully.'],
            ['th', 'เพิ่มระดับคอร์สสำเร็จ', 'อัปเดตระดับคอร์สสำเร็จ', 'ลบระดับคอร์สสำเร็จ'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function CourseLevelPageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $form_heading,
        string $list_heading,
        string $name_label,
        string $status_label,
        string $action_label,
        string $save_label
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreateLevel();

        $response = $this->actingAs($this->actor, 'web')->get(route('course.level.list'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($form_heading)
            ->assertSee($list_heading)
            ->assertSee($name_label)
            ->assertSee($status_label)
            ->assertSee($action_label)
            ->assertSee($save_label)
            ->assertDontSee('common.')
            ->assertDontSee('course.levels.');
    }

    #[Test]
    #[DataProvider('LocalizedActionMessages')]
    public function CourseLevelActionsUseTheSelectedLocale(
        string $locale,
        string $created_message,
        string $updated_message,
        string $deleted_message
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $create_response = $this->actingAs($this->actor, 'web')->postJson(route('course.level.store'), [
            'title' => [
                'en' => 'Beginner',
                'th' => 'ระดับเริ่มต้น',
            ],
        ]);

        $create_response->assertOk()->assertJson([
            'success' => true,
            'message' => $created_message,
        ]);

        $level = CourseLevel::query()->latest('id')->firstOrFail();
        $update_response = $this->actingAs($this->actor, 'web')->postJson(route('course.level.update'), [
            'id' => $level->id,
            'title' => [
                'en' => 'Intermediate',
                'th' => 'ระดับกลาง',
            ],
        ]);

        $update_response->assertOk()->assertJson([
            'success' => true,
            'message' => $updated_message,
        ]);

        $delete_response = $this->actingAs($this->actor, 'web')->delete(route('course.level.destroy', $level));

        $delete_response->assertOk()->assertJson([
            'success' => true,
            'message' => $deleted_message,
        ]);
    }

    private function CreateLevel(): CourseLevel
    {
        $level = new CourseLevel();
        $level->setTranslation('title', 'en', 'Beginner');
        $level->setTranslation('title', 'th', 'ระดับเริ่มต้น');
        $level->status = 1;
        $level->save();

        return $level;
    }
}
