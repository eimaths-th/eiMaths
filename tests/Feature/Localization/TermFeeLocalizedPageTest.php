<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use App\Models\level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TermFeeLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private level $level;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Term Fee Test HQ',
            'code' => 'TERM-FEE-TEST-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        foreach (['centre-list', 'centre-edit', 'financial-settings-manage'] as $permission_name) {
            $role->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);

        $this->level = level::create([
            'name' => 'P1',
            'price' => 7000,
            'half_price' => 3500,
            'book_price' => 3500,
            'book_half_price' => 1750,
            'status' => 1,
        ]);
    }

    public static function LocalizedLabels(): array
    {
        return [
            ['en', 'Term Fees & Book Prices', 'Level', 'Term Fee', 'Term Fee (Half)', 'Book Price', 'Book Half Price', 'Edit Fee', 'Save', 'Close'],
            ['th', 'ค่าเทอมและราคาหนังสือ', 'ระดับ', 'ค่าเทอม', 'ค่าเทอม (ครึ่งราคา)', 'ราคาหนังสือ', 'ราคาหนังสือครึ่งราคา', 'แก้ไขค่าเทอม', 'บันทึก', 'ปิด'],
        ];
    }

    public static function LocalizedMessages(): array
    {
        return [
            ['en', 'Data has been successfully edited.'],
            ['th', 'แก้ไขข้อมูลสำเร็จแล้ว'],
        ];
    }

    public static function LocalizedValidationMessages(): array
    {
        return [
            ['en', 'Term Fee must not be blank!'],
            ['th', 'กรุณาระบุค่าเทอม'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function TermFeePageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $level_label,
        string $term_fee_label,
        string $term_fee_half_label,
        string $book_price_label,
        string $book_half_price_label,
        string $edit_heading,
        string $save_label,
        string $close_label,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('termfee'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($level_label)
            ->assertSee($term_fee_label)
            ->assertSee($term_fee_half_label)
            ->assertSee($book_price_label)
            ->assertSee($book_half_price_label)
            ->assertSee($edit_heading)
            ->assertSee($save_label)
            ->assertSee($close_label)
            ->assertDontSee('term-fee.');

        $ajax_response = $this->actingAs($this->actor, 'web')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('termfee'));

        $ajax_response->assertOk();
        $this->assertStringContainsString(
            $locale === 'th' ? 'แก้ไข' : 'Edit',
            (string) data_get($ajax_response->json(), 'data.0.action'),
        );
    }

    #[Test]
    #[DataProvider('LocalizedMessages')]
    public function TermFeeUpdateUsesTheSelectedLocale(string $locale, string $updated_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->putJson(route('termfee.save', $this->level), [
            'price' => 7200,
            'half_price' => 3600,
            'book_price' => 3600,
            'book_half_price' => 1800,
        ]);

        $response->assertOk()->assertJsonPath('success', $updated_message);
        $this->assertSame('7200.00', number_format((float) $this->level->fresh()->price, 2, '.', ''));
    }

    #[Test]
    #[DataProvider('LocalizedValidationMessages')]
    public function TermFeeValidationUsesTheSelectedLocale(string $locale, string $validation_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->putJson(route('termfee.save', $this->level), []);

        $response->assertOk()->assertJsonPath('errors.0', $validation_message);
    }
}
