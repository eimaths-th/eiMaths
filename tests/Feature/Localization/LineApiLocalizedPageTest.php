<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\LineApiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LineApiLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'LINE Test HQ',
            'code' => 'LINE-TEST-HQ',
            'status' => 1,
            'branch_type' => 'hq',
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        foreach (['integration-settings-manage', 'all-centre', 'user-edit', 'user-delete'] as $permission_name) {
            $role->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);

        LineApiSetting::create([
            'name' => 'Primary LINE',
            'line_user_id' => Crypt::encryptString('U-primary'),
            'channel_secret' => Crypt::encryptString('secret-primary'),
            'channel_access_token' => Crypt::encryptString('token-primary'),
            'create_by' => $this->actor->id,
            'status' => 1,
        ]);
    }

    public static function LocalizedLabels(): array
    {
        return [
            [
                'en',
                ['LINE API Management', 'Add LINE API', 'Delete All', 'Name', 'Created at', 'Updated at', 'LINE user ID', 'Channel secret', 'Channel access token', 'Created by', 'Updated by', 'Status', 'Actions', 'Save', 'Close'],
            ],
            [
                'th',
                ['จัดการ LINE API', 'เพิ่ม LINE API', 'ลบทั้งหมด', 'ชื่อ', 'สร้างเมื่อ', 'แก้ไขเมื่อ', 'LINE user ID', 'Channel secret', 'Channel access token', 'สร้างโดย', 'แก้ไขโดย', 'สถานะ', 'การดำเนินการ', 'บันทึก', 'ปิด'],
            ],
        ];
    }

    public static function LocalizedMessages(): array
    {
        return [
            [
                'en',
                'Data stored successfully.',
                'Record updated successfully.',
                'Status updated successfully.',
                'Record deleted successfully.',
            ],
            [
                'th',
                'บันทึกข้อมูลสำเร็จ',
                'แก้ไขข้อมูลสำเร็จ',
                'อัปเดตสถานะสำเร็จ',
                'ลบข้อมูลสำเร็จ',
            ],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function LineApiPageRendersTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('line-api.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertDontSee('system-settings.line_api.');
        foreach ($labels as $label) {
            $response->assertSee($label);
        }

        $ajax_response = $this->actingAs($this->actor, 'web')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('line-api.index'));

        $ajax_response->assertOk();
        $action = (string) data_get($ajax_response->json(), 'data.0.action');
        $this->assertStringContainsString($locale === 'th' ? 'แก้ไข' : 'Edit', $action);
        $this->assertStringContainsString($locale === 'th' ? 'ลบ' : 'Delete', $action);
    }

    #[Test]
    #[DataProvider('LocalizedMessages')]
    public function LineApiActionsUseTheSelectedLocale(
        string $locale,
        string $created_message,
        string $updated_message,
        string $status_message,
        string $deleted_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $create_response = $this->actingAs($this->actor, 'web')->postJson(route('line-api.store'), [
            'name' => 'Secondary LINE',
            'line_user_id' => 'U-secondary',
            'channel_secret' => 'secret-secondary',
            'channel_access_token' => 'token-secondary',
        ]);
        $create_response->assertOk()->assertJsonPath('success', $created_message);
        $setting = LineApiSetting::where('name', 'Secondary LINE')->firstOrFail();

        $update_response = $this->actingAs($this->actor, 'web')->putJson(route('line-api.update', $setting), [
            'name' => 'Secondary LINE Updated',
            'line_user_id' => 'U-secondary-updated',
            'channel_secret' => 'secret-secondary-updated',
            'channel_access_token' => 'token-secondary-updated',
        ]);
        $update_response->assertOk()->assertJsonPath('message', $updated_message);

        $status_response = $this->actingAs($this->actor, 'web')->postJson(route('line-api.updateStatus', $setting), [
            'status' => 1,
        ]);
        $status_response->assertOk()->assertJsonPath('message', $status_message);

        $delete_response = $this->actingAs($this->actor, 'web')->deleteJson(route('line-api.destroy', $setting));
        $delete_response->assertOk()->assertJsonPath('message', $deleted_message);
    }
}
