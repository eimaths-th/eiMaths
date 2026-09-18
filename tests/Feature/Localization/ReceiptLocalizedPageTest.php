<?php

namespace Tests\Feature\Localization;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Receipt;
use App\Models\User;
use App\Models\bookuse;
use App\Models\level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceiptLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 5,
            'name' => 'Receipt Test Branch',
            'code' => 'RECEIPT',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('receipt-list', 'web'));

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
            [
                'en',
                'New Student Receipt',
                'Export Excel',
                'Date Filter:',
                'Search Type:',
                'Please Select',
                'Student Code',
                'Student Name',
                'Receipt Number',
                'Payment Date',
                'Book Use',
                'Total Fee',
                'No Data Found',
                'Search:',
            ],
            [
                'th',
                'ใบเสร็จนักเรียนใหม่',
                'ส่งออก Excel',
                'ตัวกรองวันที่:',
                'ประเภทการค้นหา:',
                'กรุณาเลือก',
                'รหัสนักเรียน',
                'ชื่อนักเรียน',
                'เลขที่ใบเสร็จ',
                'วันที่ชำระเงิน',
                'หนังสือ',
                'ค่าธรรมเนียมรวม',
                'ไม่พบข้อมูล',
                'ค้นหา:',
            ],
        ];
    }

    public static function LocalizedRowActions(): array
    {
        return [
            ['en', 'Edit', 'Print', 'PDF'],
            ['th', 'แก้ไข', 'พิมพ์', 'PDF'],
        ];
    }

    public static function LocalizedModalLabels(): array
    {
        return [
            ['en', 'Edit', 'Upload proof of payment', 'Slip image', 'Save', 'Close', 'Print', 'Remove'],
            ['th', 'แก้ไข', 'อัปโหลดหลักฐานการชำระเงิน', 'รูปสลิป', 'บันทึก', 'ปิด', 'พิมพ์', 'ลบไฟล์'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function ReceiptPageRendersTheSelectedLocale(
        string $locale,
        string $title,
        string $export_excel,
        string $date_filter,
        string $search_type,
        string $please_select,
        string $student_code,
        string $student_name,
        string $receipt_number,
        string $payment_date,
        string $book_use,
        string $total_fee,
        string $no_data,
        string $search
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('receipts', ['type' => 0]));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($title)
            ->assertSee($export_excel)
            ->assertSee($date_filter)
            ->assertSee($search_type)
            ->assertSee($please_select)
            ->assertSee($student_code)
            ->assertSee($student_name)
            ->assertSee($receipt_number)
            ->assertSee($payment_date)
            ->assertSee($book_use)
            ->assertSee($total_fee)
            ->assertSee($no_data)
            ->assertSee($search)
            ->assertDontSee('receipts.list.');
    }

    #[Test]
    #[DataProvider('LocalizedRowActions')]
    public function ReceiptRowActionsUseTheSelectedLocale(
        string $locale,
        string $edit,
        string $print,
        string $pdf
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreateReceipt();

        $response = $this->actingAs($this->actor, 'web')->getJson(route('receipts', [
            'type' => 0,
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()->assertJsonPath('recordsTotal', 1);
        $action = (string) $response->json('data.0.action');

        $this->assertStringContainsString($edit, $action);
        $this->assertStringContainsString($print, $action);
        $this->assertStringContainsString($pdf, $action);
        $this->assertStringNotContainsString('receipts.list.', $action);
    }

    #[Test]
    #[DataProvider('LocalizedModalLabels')]
    public function ReceiptModalsUseTheSelectedLocale(
        string $locale,
        string $edit,
        string $upload_proof,
        string $slip_image,
        string $save,
        string $close,
        string $print,
        string $remove
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('receipts', ['type' => 0]));

        $response->assertOk()
            ->assertSee('<h4 class="modal-title"><i class="fa-solid fa-edit"></i> ' . $edit . '</h4>', false)
            ->assertSee($edit)
            ->assertSee($upload_proof)
            ->assertSee($slip_image)
            ->assertSee($save)
            ->assertSee($close)
            ->assertSee($print)
            ->assertSee($remove)
            ->assertDontSee('receipts.list.');
    }

    private function CreateReceipt(): void
    {
        $level = level::create([
            'name' => 'P1',
            'status' => 1,
        ]);
        $book = bookuse::forceCreate([
            'level_id' => $level->id,
            'term_id' => 1,
            'name' => 'P131',
            'status' => 1,
        ]);
        $contact = Contact::create([
            'centre' => 5,
            'code' => 'ST-RECEIPT',
            'name' => 'Receipt Student',
        ]);
        $order = Order::create([
            'centre' => 5,
            'cid' => $contact->id,
            'ref' => 'REF-RECEIPT',
            'status' => 1,
            'total_price' => 100,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $book->id,
            'pname' => 'P131',
            'quantity' => 1,
            'price' => 100,
        ]);
        Receipt::forceCreate([
            'type' => 0,
            'centre' => 5,
            'cid' => $contact->id,
            'oid' => $order->id,
            'ref' => 'REF-RECEIPT',
            'student_no' => 'ST-RECEIPT',
            'student_name' => 'Receipt Student',
            'receipt_number' => 'RC-0001',
            'payment_date' => '2026-09-18',
            'level' => $level->id,
            'total_fee' => 100,
            'status' => 1,
        ]);
    }
}
