<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\QRCodePayMent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QrCodePaymentLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private QRCodePayMent $qr_code;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Headquarters',
            'code' => 'HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('financial-settings-manage', 'web'));
        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
        $this->qr_code = QRCodePayMent::create([
            'account_name' => 'eiMaths Account',
            'bank_name' => 'Demo Bank',
            'account_numbers' => '1234567890',
            'promptpay_numbers' => '0812345678',
        ]);
    }

    public static function LocalizedLabels(): array
    {
        return [
            ['en', 'QR Code Payment Management', 'Account name', 'Bank name', 'Account number', 'PromptPay number', 'Update', 'QR Code updated successfully.'],
            ['th', 'จัดการการชำระเงิน QR', 'ชื่อบัญชี', 'ชื่อธนาคาร', 'เลขที่บัญชี', 'หมายเลขพร้อมเพย์', 'อัปเดต', 'อัปเดต QR Code สำเร็จแล้ว'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function QrPaymentPageAndUpdateUseTheSelectedLocale(
        string $locale,
        string $title,
        string $account_name,
        string $bank_name,
        string $account_number,
        string $promptpay_number,
        string $update_label,
        string $updated_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $this->actingAs($this->actor)->get(route('qrcode_payment.index'))
            ->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($title)
            ->assertSee($account_name)
            ->assertSee($bank_name)
            ->assertSee($account_number)
            ->assertSee($promptpay_number)
            ->assertSee($update_label);

        $this->actingAs($this->actor)->putJson(route('qrcode_payment.update', $this->qr_code), [
            'account_name' => 'Updated Account',
            'bank_name' => 'Updated Bank',
            'account_numbers' => '9999999999',
            'promptpay_numbers' => '0899999999',
        ])
            ->assertOk()
            ->assertJsonPath('success', $updated_message);

        $this->assertSame('Updated Account', $this->qr_code->fresh()->account_name);
    }
}
