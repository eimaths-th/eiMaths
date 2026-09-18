<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\Contact;
use App\Models\bookuse;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceiptPendingLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 5,
            'name' => 'Pending Receipt Branch',
            'code' => 'PENDING',
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
                'Pending Receipt',
                'Date Filter:',
                'Search Type:',
                'Please Select',
                'Student Code',
                'Student Name',
                'Referent Code',
                'Term & Book Fee',
                'Total',
                'No Data Found',
                'Search:',
            ],
            [
                'th',
                'ใบเสร็จรอดำเนินการ',
                'ตัวกรองวันที่:',
                'ประเภทการค้นหา:',
                'กรุณาเลือก',
                'รหัสนักเรียน',
                'ชื่อนักเรียน',
                'รหัสอ้างอิง',
                'ค่าธรรมเนียมเทอมและหนังสือ',
                'รวม',
                'ไม่พบข้อมูล',
                'ค้นหา:',
            ],
        ];
    }

    public static function LocalizedRowActions(): array
    {
        return [
            ['en', 'Create Receipt', 'Cancel'],
            ['th', 'สร้างใบเสร็จ', 'ยกเลิก'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function PendingReceiptPageRendersTheSelectedLocale(
        string $locale,
        string $title,
        string $date_filter,
        string $search_type,
        string $please_select,
        string $student_code,
        string $student_name,
        string $referent_code,
        string $term_book_fee,
        string $total,
        string $no_data,
        string $search
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('receipts_pending'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($title)
            ->assertSee($date_filter)
            ->assertSee($search_type)
            ->assertSee($please_select)
            ->assertSee($student_code)
            ->assertSee($student_name)
            ->assertSee($referent_code)
            ->assertSee($term_book_fee)
            ->assertSee($total)
            ->assertSee($no_data)
            ->assertSee($search)
            ->assertDontSee('receipts.pending.');
    }

    #[Test]
    #[DataProvider('LocalizedRowActions')]
    public function PendingReceiptRowActionsUseTheSelectedLocale(
        string $locale,
        string $create_receipt,
        string $cancel
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $this->CreatePendingReceipt();

        $response = $this->actingAs($this->actor, 'web')->getJson(route('receipts_pending', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()->assertJsonPath('recordsTotal', 1);
        $action = (string) $response->json('data.0.action');

        $this->assertStringContainsString($create_receipt, $action);
        $this->assertStringContainsString($cancel, $action);
        $this->assertStringNotContainsString('receipts.pending.', $action);
    }

    private function CreatePendingReceipt(): void
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
            'code' => 'ST-PENDING',
            'name' => 'Pending Student',
        ]);
        $order = Order::create([
            'centre' => 5,
            'cid' => $contact->id,
            'ref' => 'REF-PENDING',
            'status' => 0,
            'total_price' => 100,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $book->id,
            'pname' => 'P131',
            'quantity' => 1,
            'price' => 100,
        ]);
    }
}
