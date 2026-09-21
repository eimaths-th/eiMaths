<?php

namespace Tests\Feature\Localization;

use App\Models\Blog;
use App\Models\Department;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\FooterSetting;
use App\Models\PromotionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentManagementLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Content Test Headquarters',
            'code' => 'CONTENT-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $role = Role::findOrCreate('Admin', 'web');
        foreach (['blog-list', 'blog-create', 'blog-edit', 'blog-delete', 'faq-list', 'faq-create', 'faq-edit', 'faq-delete', 'central-settings-manage'] as $permission_name) {
            $role->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    public static function Locales(): array
    {
        return [
            ['en', 'Blog Management', 'New Blog', 'Blog List', 'Thumbnail', 'Title', 'Author', 'Pen Name', 'Published', 'Actions'],
            ['th', 'จัดการบทความ', 'เพิ่มบทความ', 'รายการบทความ', 'รูปหน้าปก', 'ชื่อเรื่อง', 'ผู้เขียน', 'นามปากกา', 'เผยแพร่', 'การดำเนินการ'],
        ];
    }

    #[Test]
    #[DataProvider('Locales')]
    public function BlogPagesRenderTheSelectedLocale(
        string $locale,
        string $management_title,
        string $new_blog,
        string $list_title,
        string $thumbnail,
        string $title,
        string $author,
        string $pen_name,
        string $published,
        string $actions,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $blog = Blog::forceCreate([
            'title' => 'Localization Blog',
            'slug' => 'localization-blog-' . $locale,
            'content' => '# Content',
            'user_id' => $this->actor->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->actor, 'web')->get(route('blogs.index'));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($management_title)
            ->assertSee($new_blog)
            ->assertSee($list_title)
            ->assertSee($thumbnail)
            ->assertSee($title)
            ->assertSee($author)
            ->assertSee($pen_name)
            ->assertSee($published)
            ->assertSee($actions)
            ->assertDontSee('content-management.');

        $create_response = $this->actingAs($this->actor, 'web')->get(route('blogs.create'));
        $create_response->assertOk()->assertSee($locale === 'en' ? 'Create New Blog' : 'สร้างบทความใหม่');

        $edit_response = $this->actingAs($this->actor, 'web')->get(route('blogs.edit', $blog));
        $edit_response->assertOk()->assertSee($locale === 'en' ? 'Edit Blog' : 'แก้ไขบทความ');

        $show_response = $this->actingAs($this->actor, 'web')->get(route('blogs.show', $blog));
        $show_response->assertOk()->assertSee($locale === 'en' ? 'Author:' : 'ผู้เขียน:');

        $ajax_response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->actingAs($this->actor, 'web')
            ->get(route('blogs.index'));
        $ajax_response->assertOk();
        $action = (string) $ajax_response->json('data.0.action');
        $this->assertStringContainsString($locale === 'en' ? 'View' : 'ดู', $action);
        $this->assertStringContainsString($locale === 'en' ? 'Edit' : 'แก้ไข', $action);
        $this->assertStringContainsString($locale === 'en' ? 'Delete' : 'ลบ', $action);
    }

    public static function FaqLocales(): array
    {
        return [
            ['en', 'FAQ Management', 'Manage Categories', 'New FAQ', 'FAQ List', 'Category', 'Question', 'Answer', 'Published', 'Actions'],
            ['th', 'จัดการคำถามที่พบบ่อย', 'จัดการหมวดหมู่', 'เพิ่มคำถามที่พบบ่อย', 'รายการคำถามที่พบบ่อย', 'หมวดหมู่', 'คำถาม', 'คำตอบ', 'เผยแพร่', 'การดำเนินการ'],
        ];
    }

    #[Test]
    #[DataProvider('FaqLocales')]
    public function FaqPagesRenderTheSelectedLocale(
        string $locale,
        string $management_title,
        string $manage_categories,
        string $new_faq,
        string $list_title,
        string $category,
        string $question,
        string $answer,
        string $published,
        string $actions,
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $faq_category = FaqCategory::forceCreate([
            'name' => 'ทั่วไป',
            'name_en' => 'General',
            'slug' => 'general-' . $locale,
            'is_published' => true,
        ]);
        Faq::forceCreate([
            'category_id' => $faq_category->id,
            'question' => 'คำถาม',
            'question_en' => 'Question',
            'answer' => 'คำตอบ',
            'answer_en' => 'Answer',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->actor, 'web')->get(route('faqs.index'));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($management_title)
            ->assertSee($manage_categories)
            ->assertSee($new_faq)
            ->assertSee($list_title)
            ->assertSee($category)
            ->assertSee($question)
            ->assertSee($answer)
            ->assertSee($published)
            ->assertSee($actions)
            ->assertDontSee('content-management.');

        $create_response = $this->actingAs($this->actor, 'web')->get(route('faqs.create'));
        $create_response->assertOk()->assertSee($locale === 'en' ? 'Create New FAQ' : 'สร้างคำถามที่พบบ่อย');

        $category_response = $this->actingAs($this->actor, 'web')->get(route('faq-categories.index'));
        $category_response->assertOk()->assertSee($locale === 'en' ? 'FAQ Categories' : 'หมวดหมู่คำถามที่พบบ่อย');

        $category_create_response = $this->actingAs($this->actor, 'web')->get(route('faq-categories.create'));
        $category_create_response->assertOk()->assertSee($locale === 'en' ? 'Create FAQ Category' : 'สร้างหมวดหมู่คำถามที่พบบ่อย');
    }

    public static function SupportingPages(): array
    {
        return [
            ['en', 'Promotion Image Management', 'Upload Content', 'Promotion Images', 'Footer Setting Management', 'Update'],
            ['th', 'จัดการแบนเนอร์ประชาสัมพันธ์', 'อัปโหลดเนื้อหา', 'แบนเนอร์ประชาสัมพันธ์', 'จัดการการตั้งค่าฟุตเตอร์', 'อัปเดต'],
        ];
    }

    #[Test]
    #[DataProvider('SupportingPages')]
    public function PromotionAndFooterPagesRenderTheSelectedLocale(
        string $locale,
        string $promotion_title,
        string $upload_content,
        string $promotion_list,
        string $footer_title,
        string $update,
    ): void {
        $this->actor->update(['language_code' => $locale]);
        FooterSetting::forceCreate([
            'title' => 'Footer title',
            'description' => 'Footer description',
            'description2' => 'Footer description 2',
        ]);
        PromotionImage::forceCreate([
            'img' => 'img/promotion/test.jpg',
            'title' => 'Promotion',
            'type' => 'image',
            'status' => 'disabled',
            'is_for_affiliate' => false,
        ]);

        $promotion_response = $this->actingAs($this->actor, 'web')->get(route('promotion-img.index'));
        $promotion_response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($promotion_title)
            ->assertSee($upload_content)
            ->assertSee($promotion_list)
            ->assertDontSee('content-management.');

        $footer_response = $this->actingAs($this->actor, 'web')->get(route('footer-setting.index'));
        $footer_response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($footer_title)
            ->assertSee($update)
            ->assertDontSee('content-management.');
    }
}
