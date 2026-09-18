<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\CourseSetting\Entities\Category;
use Modules\CourseSetting\Entities\Course;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $hq = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Course Test HQ',
            'code' => 'COURSE-TEST-HQ',
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
            ['en', 'Courses List', 'Add Course', 'Course List', 'Course title', 'Course status', 'Save course'],
            ['th', 'รายการคอร์ส', 'เพิ่มคอร์ส', 'รายการคอร์ส', 'ชื่อคอร์ส', 'สถานะคอร์ส', 'บันทึกคอร์ส'],
        ];
    }

    public static function LocalizedDataLabels(): array
    {
        return [
            ['en', 'Course', 'Online', 'Public', 'Actions', 'Edit', 'Delete'],
            ['th', 'คอร์ส', 'ออนไลน์', 'สาธารณะ', 'การทำงาน', 'แก้ไข', 'ลบ'],
        ];
    }

    public static function LocalizedActionMessages(): array
    {
        return [
            ['en', 'Course updated successfully.', 'Status updated successfully.', 'Course and image deleted successfully.'],
            ['th', 'อัปเดตคอร์สสำเร็จ', 'อัปเดตสถานะสำเร็จ', 'ลบคอร์สและภาพสำเร็จ'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function CoursePageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $add_course,
        string $list_heading,
        string $title_label,
        string $status_label,
        string $save_label
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreateCategory();

        $response = $this->actingAs($this->actor, 'web')->get(route('course.list'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($add_course)
            ->assertSee($list_heading)
            ->assertSee($title_label)
            ->assertSee($status_label)
            ->assertSee($save_label)
            ->assertDontSee('course.courses.');
    }

    #[Test]
    #[DataProvider('LocalizedDataLabels')]
    public function CourseDataUsesTheSelectedLocale(
        string $locale,
        string $type_label,
        string $delivery_label,
        string $scope_label,
        string $actions_label,
        string $edit_label,
        string $delete_label
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $category = $this->CreateCategory();
        $course = new Course();
        $course->category_id = $category->id;
        $course->user_id = $this->actor->id;
        $course->lang_id = 19;
        $course->setTranslation('title', 'en', 'Algebra');
        $course->setTranslation('title', 'th', 'พีชคณิต');
        $course->price = 100;
        $course->status = 1;
        $course->publish = 1;
        $course->type = 1;
        $course->mode_of_delivery = 1;
        $course->scope = 1;
        $course->slug = 'algebra';
        $course->save();

        $response = $this->actingAs($this->actor, 'web')
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('course.list'));

        $response->assertOk()
            ->assertJsonPath('data.0.type', $type_label)
            ->assertJsonPath('data.0.mode_of_delivery', $delivery_label)
            ->assertJsonPath('data.0.scope', $scope_label);
        $action_html = (string) $response->json('data.0.action');
        $this->assertStringContainsString($actions_label, $action_html);
        $this->assertStringContainsString($edit_label, $action_html);
        $this->assertStringContainsString($delete_label, $action_html);
    }

    #[Test]
    #[DataProvider('LocalizedActionMessages')]
    public function CourseActionsUseTheSelectedLocale(
        string $locale,
        string $updated_message,
        string $status_updated_message,
        string $deleted_message
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $category = $this->CreateCategory();
        $course = $this->CreateCourse($category);

        $update_response = $this->actingAs($this->actor, 'web')->put(route('course.update', $course), [
            'title' => $locale === 'th' ? 'พีชคณิตพื้นฐาน' : 'Foundations of Algebra',
            'category_id' => $category->id,
            'mode_of_delivery' => 1,
            'price' => 100,
            'status' => 1,
            'about' => '',
            'outcomes' => '',
            'requirements' => '',
        ]);

        $update_response->assertOk()->assertJson([
            'success' => true,
            'message' => $updated_message,
        ]);

        $status_response = $this->actingAs($this->actor, 'web')->post(route('courses.updateStatus'), [
            'id' => $course->id,
            'status' => 0,
        ]);

        $status_response->assertOk()->assertJson([
            'success' => true,
            'message' => $status_updated_message,
        ]);

        $delete_response = $this->actingAs($this->actor, 'web')->delete(route('course.destroy', $course));

        $delete_response->assertOk()->assertJson([
            'success' => true,
            'message' => $deleted_message,
        ]);
    }

    private function CreateCategory(): Category
    {
        $category = new Category();
        $category->setTranslation('name', 'en', 'Mathematics');
        $category->setTranslation('name', 'th', 'คณิตศาสตร์');
        $category->setTranslation('description', 'en', 'Mathematics');
        $category->setTranslation('description', 'th', 'คณิตศาสตร์');
        $category->status = 1;
        $category->position_order = 1;
        $category->user_id = $this->actor->id;
        $category->save();

        return $category;
    }

    private function CreateCourse(Category $category): Course
    {
        $course = new Course();
        $course->category_id = $category->id;
        $course->user_id = $this->actor->id;
        $course->lang_id = 19;
        $course->setTranslation('title', 'en', 'Algebra');
        $course->setTranslation('title', 'th', 'พีชคณิต');
        $course->price = 100;
        $course->status = 1;
        $course->publish = 1;
        $course->type = 1;
        $course->mode_of_delivery = 1;
        $course->scope = 1;
        $course->slug = 'algebra';
        $course->save();

        return $course;
    }
}
