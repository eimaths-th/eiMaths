<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Department $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Product Test Branch',
            'code' => 'PRODUCT',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $role = Role::findOrCreate('BranchAdmin', 'web');
        foreach (['product-list', 'product-create', 'product-edit', 'product-delete', 'product-show'] as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $this->actor = User::factory()->create([
            'department_id' => $this->branch->id,
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
                'Education Accessories',
                'Receive Stock',
                'Issue Stock',
                'Print Products',
                'Add Accessory',
                'Delete All',
                'Print Options',
                'Select Centre',
                'Select Code Type',
                'Code',
                'Branch',
                'Name',
                'In Stock',
                'Unit',
                'Stock History',
                'Transaction Date',
                'Source Centre',
                'Destination',
                'Accessory',
                'Received',
                'Issued',
                'Remaining',
                'Operator',
                'Edit Accessory',
                'Accessory Code',
                'Save',
                'Close',
                'Barcode',
                'QR Code',
            ],
            [
                'th',
                'อุปกรณ์การศึกษา',
                'รับเข้าสินค้า',
                'จ่ายสินค้าออก',
                'พิมพ์รายการสินค้า',
                'เพิ่มอุปกรณ์',
                'ลบทั้งหมด',
                'ตัวเลือกการพิมพ์',
                'เลือกสาขา',
                'เลือกประเภทโค้ด',
                'รหัส',
                'สาขา',
                'ชื่อ',
                'คงเหลือ',
                'หน่วย',
                'ประวัติสต็อก',
                'วันที่ทำรายการ',
                'สาขาต้นทาง',
                'ปลายทาง',
                'อุปกรณ์',
                'รับเข้า',
                'จ่ายออก',
                'คงเหลือ',
                'ผู้ทำรายการ',
                'แก้ไขอุปกรณ์',
                'รหัสอุปกรณ์',
                'บันทึก',
                'ปิด',
                'บาร์โค้ด',
                'QR Code',
            ],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedLabels')]
    public function ProductsPageRendersTheSelectedLocale(
        string $locale,
        string $title,
        string $receive_stock,
        string $issue_stock,
        string $print_products,
        string $add_accessory,
        string $delete_all,
        string $print_options,
        string $select_centre,
        string $select_code_type,
        string $code,
        string $branch,
        string $name,
        string $in_stock,
        string $unit,
        string $stock_history,
        string $transaction_date,
        string $source_centre,
        string $destination,
        string $accessory,
        string $received,
        string $issued,
        string $remaining,
        string $operator,
        string $edit_accessory,
        string $accessory_code,
        string $save,
        string $close,
        string $barcode,
        string $qrcode
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('products'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($title)
            ->assertSee($receive_stock)
            ->assertSee($issue_stock)
            ->assertSee($print_products)
            ->assertSee($add_accessory)
            ->assertSee($delete_all)
            ->assertSee($print_options)
            ->assertSee($select_centre)
            ->assertSee($select_code_type)
            ->assertSee($code)
            ->assertSee($branch)
            ->assertSee($name)
            ->assertSee($in_stock)
            ->assertSee($unit)
            ->assertSee($stock_history)
            ->assertSee($transaction_date)
            ->assertSee($source_centre)
            ->assertSee($destination)
            ->assertSee($accessory)
            ->assertSee($received)
            ->assertSee($issued)
            ->assertSee($remaining)
            ->assertSee($operator)
            ->assertSee($edit_accessory)
            ->assertSee($accessory_code)
            ->assertSee($save)
            ->assertSee($close)
            ->assertSee($barcode)
            ->assertSee($qrcode)
            ->assertDontSee('products.');
    }

    public static function LocalizedRowActions(): array
    {
        return [
            ['en', 'Log', 'Barcode', 'QR Code', 'Edit', 'Delete'],
            ['th', 'ประวัติ', 'บาร์โค้ด', 'QR Code', 'แก้ไข', 'ลบ'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedRowActions')]
    public function ProductRowActionsUseTheSelectedLocale(
        string $locale,
        string $log,
        string $barcode,
        string $qrcode,
        string $edit,
        string $delete
    ): void {
        $this->actor->update(['language_code' => $locale]);
        Product::forceCreate([
            'centre' => $this->branch->id,
            'code' => 'PRD001',
            'name' => 'Marker',
            'detail' => 'Whiteboard marker',
            'unit' => 'piece',
            'amount' => 10,
        ]);

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->actingAs($this->actor, 'web')
            ->get(route('products', ['draw' => 1, 'start' => 0, 'length' => 10]));

        $response->assertOk();
        $action = (string) $response->json('data.0.action');
        $this->assertStringContainsString($log, $action);
        $this->assertStringContainsString($barcode, $action);
        $this->assertStringContainsString($qrcode, $action);
        $this->assertStringContainsString($edit, $action);
        $this->assertStringContainsString($delete, $action);
        $this->assertStringNotContainsString('products.', $action);
    }

    public static function LocalizedMessages(): array
    {
        return [
            ['en', 'The accessory code is required.'],
            ['th', 'กรุณาระบุรหัสอุปกรณ์'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedMessages')]
    public function ProductStoreMessagesUseTheSelectedLocale(string $locale, string $created_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->postJson(route('products.store'), [
            'name' => 'Whiteboard Marker',
            'centre' => $this->branch->id,
            'unit' => 'piece',
            'amount' => 10,
            'detail' => 'Marker for classroom use',
        ]);

        $response->assertOk()->assertJsonPath('errors.0', $created_message);
    }

    public static function PrintLabels(): array
    {
        return [
            [
                'en',
                'Products and Accessories',
                'Print',
                'Printed at:',
                'Total items:',
                'Detail:',
                'In stock:',
                'Scan the barcode below',
            ],
            [
                'th',
                'รายการสินค้าและอุปกรณ์',
                'พิมพ์',
                'วันที่พิมพ์:',
                'จำนวนรายการทั้งหมด:',
                'รายละเอียด:',
                'มีในสต็อก:',
                'สแกนบาร์โค้ดด้านล่าง',
            ],
        ];
    }

    #[Test]
    #[DataProvider('PrintLabels')]
    public function ProductPrintPageRendersTheSelectedLocale(
        string $locale,
        string $heading,
        string $print,
        string $printed_at,
        string $total_items,
        string $detail,
        string $in_stock,
        string $scan_barcode
    ): void {
        $this->actor->update(['language_code' => $locale]);
        Product::forceCreate([
            'centre' => $this->branch->id,
            'code' => 'PRN001',
            'name' => 'Marker',
            'detail' => 'Classroom marker',
            'unit' => 'piece',
            'amount' => 3,
        ]);

        $response = $this->actingAs($this->actor, 'web')->get(route('products.print', [
            'type' => 'view',
            'centre' => $this->branch->id,
            'code_type' => 'barcode',
        ]));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee($print)
            ->assertSee($printed_at)
            ->assertSee($total_items)
            ->assertSee($detail)
            ->assertSee($in_stock)
            ->assertSee($scan_barcode)
            ->assertDontSee('products.print.');
    }
}
