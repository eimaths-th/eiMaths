<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeedbackLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Feedback Test Headquarters',
            'code' => 'FEEDBACK-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('support-manage', 'web'));
        $this->actor->assignRole($role);
    }

    public static function Locales(): array
    {
        return [
            [
                'en',
                [
                    'Feedback List',
                    'New Feedback',
                    'No.',
                    'Title',
                    'Category',
                    'Status',
                    'Priority',
                    'Action',
                    'View',
                    'Create Feedback',
                    'Description:',
                    'Submit Feedback',
                    'Feedback View Details',
                    'View Feedback',
                    'Submitted by:',
                    'Back to Feedback List',
                    'Bug',
                    'New',
                    'High',
                ],
            ],
            [
                'th',
                [
                    'รายการข้อเสนอแนะ',
                    'เพิ่มข้อเสนอแนะ',
                    'ลำดับ',
                    'หัวข้อ',
                    'หมวดหมู่',
                    'สถานะ',
                    'ความสำคัญ',
                    'การดำเนินการ',
                    'ดู',
                    'สร้างข้อเสนอแนะ',
                    'รายละเอียด:',
                    'ส่งข้อเสนอแนะ',
                    'รายละเอียดข้อเสนอแนะ',
                    'ดูข้อเสนอแนะ',
                    'ส่งโดย:',
                    'กลับไปรายการข้อเสนอแนะ',
                    'ข้อผิดพลาด',
                    'ใหม่',
                    'สูง',
                ],
            ],
        ];
    }

    public static function MessageLocales(): array
    {
        return [
            ['en', 'Feedback submitted successfully.', 'Feedback updated successfully.'],
            ['th', 'ส่งข้อเสนอแนะสำเร็จ', 'อัปเดตข้อเสนอแนะสำเร็จ'],
        ];
    }

    #[Test]
    #[DataProvider('Locales')]
    public function FeedbackPagesRenderTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $feedback = Feedback::forceCreate([
            'user_id' => $this->actor->id,
            'title' => 'Login issue',
            'description' => 'The login page is difficult to use.',
            'category' => 'Bug',
            'status' => 'New',
            'priority' => 'High',
        ]);

        $list_response = $this->actingAs($this->actor, 'web')->get(route('feedback.index'));
        $list_response->assertOk()->assertSee('<html lang="' . $locale . '">', false);

        $create_response = $this->actingAs($this->actor, 'web')->get(route('feedback.create'));
        $create_response->assertOk()->assertSee('<html lang="' . $locale . '">', false);

        $detail_response = $this->actingAs($this->actor, 'web')->get(route('feedback.show', $feedback));
        $detail_response->assertOk()->assertSee('<html lang="' . $locale . '">', false);

        $page_content = $list_response->getContent() . $create_response->getContent() . $detail_response->getContent();
        foreach ($labels as $label) {
            $this->assertStringContainsString($label, $page_content, "Expected localized Feedback label [{$label}] was not rendered.");
        }

        $this->assertStringNotContainsString('feedback-management.', $page_content);

        if ($locale === 'th') {
            $this->assertStringNotContainsString('Feedback List', $page_content);
            $this->assertStringNotContainsString('Create Feedback', $page_content);
            $this->assertStringNotContainsString('Feedback View Details', $page_content);
        }
    }

    #[Test]
    #[DataProvider('MessageLocales')]
    public function FeedbackActionsUseTheSelectedLocale(string $locale, string $submitted_message, string $updated_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $create_response = $this->actingAs($this->actor, 'web')->post(route('feedback.store'), [
            'title' => 'Localized feedback',
            'description' => 'A localized feedback description.',
            'category' => 'Suggestion',
            'priority' => 'Medium',
        ]);

        $create_response->assertRedirect(route('feedback.index'))
            ->assertSessionHas('success', $submitted_message);

        $feedback = Feedback::query()->latest('id')->firstOrFail();
        $update_response = $this->actingAs($this->actor, 'web')->post(route('feedback.updateStatus', $feedback), [
            'status' => 'Resolved',
            'admin_comments' => 'Completed.',
        ]);

        $update_response->assertRedirect(route('feedback.index'))
            ->assertSessionHas('success', $updated_message);
    }
}
