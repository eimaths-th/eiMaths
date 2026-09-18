<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrackingLinkLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'name' => 'Tracking Test Branch',
            'code' => 'TRACKING-TEST',
            'status' => 1,
            'enabled_modules' => ['crm'],
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo([
            Permission::findOrCreate('crm-leads-menu-view', 'web'),
            Permission::findOrCreate('crm-leads-create', 'web'),
            Permission::findOrCreate('crm-leads-delete', 'web'),
        ]);

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
            ['en', 'Tracking Link Generator', 'Create UTM Link', 'Link History', 'Generate & Save'],
            ['th', 'สร้าง Tracking Link', 'สร้างลิงก์ UTM', 'ประวัติลิงก์', 'สร้างและบันทึก'],
        ];
    }

    public static function LocalizedFlashMessages(): array
    {
        return [
            ['en', 'Tracking link created successfully.', 'Tracking link deleted successfully.'],
            ['th', 'สร้าง Tracking Link สำเร็จ', 'ลบ Tracking Link สำเร็จ'],
        ];
    }

    public static function LocalizedReportLabels(): array
    {
        return [
            ['en', 'Tracking Link Report', 'Tracking Link Generator', 'Link creator', 'Tracking Links', 'Results by Link'],
            ['th', 'รายงาน Tracking Link', 'สร้าง Tracking Link', 'ผู้สร้างลิงก์', 'Tracking Link ทั้งหมด', 'ผลลัพธ์ตามลิงก์'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function TrackingLinkGeneratorRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $form_heading,
        string $history_heading,
        string $submit_label
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('tracking-links.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($form_heading)
            ->assertSee($history_heading)
            ->assertSee($submit_label)
            ->assertDontSee('crm.tracking_links.');
    }

    #[Test]
    #[DataProvider('LocalizedFlashMessages')]
    public function TrackingLinkActionsUseTheSelectedLocale(
        string $locale,
        string $created_message,
        string $deleted_message
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $create_response = $this->actingAs($this->actor, 'web')->post(route('tracking-links.store'), [
            'name' => 'Test campaign link',
            'base_url' => 'https://eimaths-th.com/free-trial',
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'test_campaign',
        ]);

        $create_response->assertRedirect(route('tracking-links.index'))
            ->assertSessionHas('success', $created_message);

        $tracking_link = TrackingLink::query()->latest('id')->firstOrFail();
        $delete_response = $this->actingAs($this->actor, 'web')->delete(route('tracking-links.destroy', $tracking_link));

        $delete_response->assertRedirect(route('tracking-links.index'))
            ->assertSessionHas('success', $deleted_message);
    }

    #[Test]
    #[DataProvider('LocalizedReportLabels')]
    public function TrackingLinkReportRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $generator_link,
        string $creator_label,
        string $summary_label,
        string $results_heading
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('tracking-links.report'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($generator_link)
            ->assertSee($creator_label)
            ->assertSee($summary_label)
            ->assertSee($results_heading)
            ->assertDontSee('crm.tracking_links.report.');
    }
}
