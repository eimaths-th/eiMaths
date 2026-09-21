<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateManagementLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Affiliate Test Headquarters',
            'code' => 'AFF-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $this->actor = User::factory()->create([
            'department_id' => Department::HQ_ID,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);

        foreach ([
            'affiliate-course-application-list',
            'affiliate-list',
            'affiliate-withdrawal-list',
        ] as $permission_name) {
            $this->actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        config([
            'affiliate.features.administration' => true,
            'affiliate.features.wallet_withdrawals' => true,
        ]);
    }

    public static function Locales(): array
    {
        return [
            [
                'en',
                [
                    'Affiliate Course Applications',
                    'Add Course',
                    'Course Pending Details',
                    'Parent Information',
                    'Affiliate account administration',
                    'Affiliate withdrawal management',
                    'Review requests, approve eligible withdrawals, and record payment after the transfer is completed.',
                    'Join our Affiliate program',
                    'Recommend eiMaths courses and earn commission',
                ],
            ],
            [
                'th',
                [
                    'ใบสมัครคอร์สผ่าน Affiliate',
                    'เพิ่มคอร์ส',
                    'รายละเอียดใบสมัครคอร์ส',
                    'ข้อมูลผู้ปกครอง',
                    'จัดการบัญชี Affiliate',
                    'จัดการการถอนเงิน Affiliate',
                    'ตรวจสอบคำขอ อนุมัติรายการที่เข้าเงื่อนไข และบันทึกการจ่ายเงินหลังโอนสำเร็จ',
                    'เข้าร่วมโปรแกรม Affiliate ของเรา',
                    'แนะนำคอร์สเรียน eiMaths และรับค่าคอมมิชชั่น',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('Locales')]
    public function AffiliateManagementPagesRenderTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $course_applications = $this->actingAs($this->actor, 'web')->get(route('courses.pending.index'));
        $course_applications->assertOk()->assertSee('lang="' . $locale . '"', false);

        $accounts = $this->actingAs($this->actor, 'web')->get(route('affiliate.admin.index'));
        $accounts->assertOk()->assertSee('lang="' . $locale . '"', false);

        $withdrawals = $this->actingAs($this->actor, 'web')->get(route('affiliate.withdrawals.admin.index'));
        $withdrawals->assertOk()->assertSee('lang="' . $locale . '"', false);

        $website = $this->actingAs($this->actor, 'web')->get(route('affiliate.index'));
        $website->assertOk()->assertSee('lang="' . $locale . '"', false);

        foreach ($labels as $label) {
            $this->assertTrue(
                str_contains($course_applications->getContent(), $label)
                || str_contains($accounts->getContent(), $label)
                || str_contains($withdrawals->getContent(), $label)
                || str_contains($website->getContent(), $label),
                "Expected localized Affiliate label [{$label}] was not rendered."
            );
        }

        $this->assertStringNotContainsString('affiliate-management.', $course_applications->getContent());
        $this->assertStringNotContainsString('affiliate-management.', $accounts->getContent());
        $this->assertStringNotContainsString('affiliate-management.', $withdrawals->getContent());
        $this->assertStringNotContainsString('affiliate-management.', $website->getContent());
    }
}
