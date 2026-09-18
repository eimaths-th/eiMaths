<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Modules\CourseSetting\Entities\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseCategoryLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $hq = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Course Category HQ',
            'code' => 'COURSE-CATEGORY-HQ',
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
                'en', 'Category', 'Add New Category', 'Category List', 'Save',
                'Name', 'Parent', 'Position Order', 'Description', 'Status', 'Action', 'Select', 'Active', 'Inactive',
            ],
            [
                'th', 'หมวดหมู่', 'เพิ่มหมวดหมู่ใหม่', 'รายการหมวดหมู่', 'บันทึก',
                'ชื่อ', 'หมวดหมู่หลัก', 'ลำดับการแสดงผล', 'รายละเอียด', 'สถานะ', 'การทำงาน', 'เลือก', 'ใช้งาน', 'ไม่ใช้งาน',
            ],
        ];
    }

    public static function LocalizedActionMessages(): array
    {
        return [
            ['en', 'Operation successful', 'Success', 'Category deleted successfully.'],
            ['th', 'ดำเนินการสำเร็จ', 'สำเร็จ', 'ลบหมวดหมู่สำเร็จ'],
        ];
    }

    public static function LocalizedNotFoundMessages(): array
    {
        return [
            ['en', 'Category not found.'],
            ['th', 'ไม่พบหมวดหมู่'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function CourseCategoryPageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $form_heading,
        string $list_heading,
        string $save_label,
        string $name_label,
        string $parent_label,
        string $position_label,
        string $description_label,
        string $status_label,
        string $action_label,
        string $select_label,
        string $active_label,
        string $inactive_label
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreateCategory($locale);

        $response = $this->actingAs($this->actor, 'web')->get(route('categories.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($form_heading)
            ->assertSee($list_heading)
            ->assertSee($save_label)
            ->assertSee($name_label)
            ->assertSee($parent_label)
            ->assertSee($position_label)
            ->assertSee($description_label)
            ->assertSee($status_label)
            ->assertSee($action_label)
            ->assertSee($select_label)
            ->assertSee($active_label)
            ->assertSee($inactive_label)
            ->assertDontSee('course.categories.');
    }

    private function CreateCategory(string $locale): Category
    {
        $category = new Category();
        $category->setTranslation('name', $locale, $locale === 'th' ? 'พีชคณิต' : 'Algebra');
        $category->setTranslation('description', $locale, $locale === 'th' ? 'คำอธิบาย' : 'Description');
        $category->status = 1;
        $category->position_order = 1;
        $category->user_id = $this->actor->id;
        $category->save();

        return $category;
    }

    #[Test]
    #[DataProvider('LocalizedActionMessages')]
    public function CategoryActionsUseTheSelectedLocale(
        string $locale,
        string $created_message,
        string $success_title,
        string $deleted_message
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $create_response = $this->from(route('categories.index'))
            ->actingAs($this->actor, 'web')
            ->post(route('categories.store'), [
            'name' => [$locale => $locale === 'th' ? 'พีชคณิต' : 'Algebra'],
            'description' => [$locale => $locale === 'th' ? 'คำอธิบาย' : 'Description'],
            'status' => 1,
            'position_order' => 1,
            'color' => '#123456',
        ]);

        $create_response->assertRedirect(route('categories.index'));
        $toastr_messages = session('toastr::messages');
        $this->assertSame($created_message, $toastr_messages[0]['message']);
        $this->assertSame($success_title, $toastr_messages[0]['title']);

        $category = Category::query()->latest('id')->firstOrFail();
        $delete_response = $this->actingAs($this->actor, 'web')->delete(route('category.delete', $category));

        $delete_response->assertOk()->assertJson([
            'success' => true,
            'message' => $deleted_message,
        ]);
    }

    #[Test]
    #[DataProvider('LocalizedNotFoundMessages')]
    public function MissingCategoryDeleteUsesTheSelectedLocale(string $locale, string $not_found_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->delete(route('category.delete', 999999));

        $response->assertNotFound()->assertJson([
            'success' => false,
            'message' => $not_found_message,
        ]);
    }
}
