<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParameterLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private int $parameter_id;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Parameter Test HQ',
            'code' => 'PARAMETER-TEST-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        foreach (['centre-list', 'centre-edit', 'central-settings-manage'] as $permission_name) {
            $role->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);

        $this->parameter_id = DB::table('parameter')->insertGetId([
            'name' => 'Term Fee',
            'name_th' => 'ค่าเทอม',
            'price' => 7000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function LocalizedLabels(): array
    {
        return [
            ['en', 'Parameter Settings', 'Name', 'Price', 'Action', 'Edit Parameter', 'Save', 'Close'],
            ['th', 'การตั้งค่าพารามิเตอร์', 'ชื่อ', 'ราคา', 'การดำเนินการ', 'แก้ไขพารามิเตอร์', 'บันทึก', 'ปิด'],
        ];
    }

    public static function LocalizedMessages(): array
    {
        return [
            ['en', 'Data has been successfully edited.', 'Price must not be blank!'],
            ['th', 'แก้ไขข้อมูลสำเร็จแล้ว', 'กรุณาระบุราคา'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function ParameterPageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $name_label,
        string $price_label,
        string $action_label,
        string $edit_heading,
        string $save_label,
        string $close_label,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('parameter'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($name_label)
            ->assertSee($price_label)
            ->assertSee($action_label)
            ->assertSee($edit_heading)
            ->assertSee($save_label)
            ->assertSee($close_label)
            ->assertDontSee('parameter-settings.');

        $ajax_response = $this->actingAs($this->actor, 'web')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('parameter'));

        $ajax_response->assertOk();
        $this->assertStringContainsString(
            $locale === 'th' ? 'แก้ไข' : 'Edit',
            (string) data_get($ajax_response->json(), 'data.0.action'),
        );
    }

    #[Test]
    #[DataProvider('LocalizedMessages')]
    public function ParameterUpdateUsesTheSelectedLocale(
        string $locale,
        string $updated_message,
        string $validation_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->putJson(
            route('parameter.save', $this->parameter_id),
            ['price' => 7200],
        );

        $response->assertOk()->assertJsonPath('success', $updated_message);
        $this->assertSame('7200.00', number_format((float) DB::table('parameter')->where('id', $this->parameter_id)->value('price'), 2, '.', ''));

        $validation_response = $this->actingAs($this->actor, 'web')->putJson(
            route('parameter.save', $this->parameter_id),
            [],
        );

        $validation_response->assertOk()->assertJsonPath('errors.0', $validation_message);
    }
}
