<?php

namespace Tests\Feature\Receipt;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Http\Controllers\ReceiptController;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReceiptPriceGroupCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.commission_creation', true);
    }

    public function test_paid_order_generation_persists_grouped_receipt_details(): void
    {
        [$department, $contact, $order] = $this->CreatePriceGroupOrder('0.00');
        $actor = $this->CreateOrderEditor($department);

        $response = $this->actingAs($actor)->put(route('orders.save', $order->id), [
            'order_number' => 'ORD-PRICE-GROUP',
            'cid' => $contact->id,
            'ref' => null,
            'courses_pending_id' => null,
            'total_cost' => '30950.00',
            'detail' => 'Price group regression fixture',
            'refund' => '0.00',
            'register_fee' => '750.00',
            'access_fee' => '500.00',
            'orther_fee' => '0.00',
            'discount' => '0.00',
            'payment' => 1,
            'discount_book' => '0.00',
            'status' => 1,
        ]);

        $response->assertOk()->assertJson([
            'success' => 'Save Data completed',
            'status' => 1,
        ]);

        $receipt = Receipt::query()->where('oid', $order->id)->sole();
        $this->assertSame('30950.00', $this->NormalizeDecimal((string) $order->fresh()->total_price));
        $this->assertSame('30950.00', $this->NormalizeDecimal((string) $receipt->total_fee));
        $this->assertSame([
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 2, 'price' => '3000.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value],
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 3, 'price' => '4000.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value],
            ['description' => 'ค่าหนังสือ', 'quantity' => 2, 'price' => '1500.00', 'discount' => '0.00', 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value],
            ['description' => 'ค่าหนังสือ', 'quantity' => 3, 'price' => '2900.00', 'discount' => '0.00', 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value],
            ['description' => 'ค่าลงทะเบียนแรกเข้า', 'quantity' => 1, 'price' => '750.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::REGISTRATION_FEE->value],
            ['description' => 'ค่าอุปกรณ์การเรียน', 'quantity' => 1, 'price' => '500.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::EQUIPMENT->value],
        ], $this->SummarizeDetails($receipt));
    }

    public function test_receipt_detail_regeneration_uses_the_same_grouped_calculation(): void
    {
        [$department, $contact, $order] = $this->CreatePriceGroupOrder('30950.00');
        $receipt = Receipt::create([
            'centre' => $department->id,
            'cid' => $contact->id,
            'oid' => $order->id,
            'receipt_number' => 'R-PRICE-GROUP',
            'payment_date' => '2026-08-12',
            'status' => 1,
            'payment' => 1,
            'total_fee' => '30950.00',
        ]);
        $old_detail = new receipt_details();
        $old_detail->forceFill([
            'receipt_id' => $receipt->id,
            'des' => 'old generated line',
            'unit' => '',
            'quantity' => 1,
            'price' => '35750.00',
            'discount' => '0.00',
            'tax' => 1,
        ])->save();

        $order_total_before = $this->NormalizeDecimal((string) $order->total_price);
        $receipt_total_before = $this->NormalizeDecimal((string) $receipt->total_fee);

        app(ReceiptController::class)->gen_receipt_data($receipt->id);

        $this->assertSame($order_total_before, $this->NormalizeDecimal((string) $order->fresh()->total_price));
        $this->assertSame($receipt_total_before, $this->NormalizeDecimal((string) $receipt->fresh()->total_fee));
        $this->assertSame([
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 2, 'price' => '3000.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value],
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 3, 'price' => '4000.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value],
            ['description' => 'ค่าหนังสือ', 'quantity' => 2, 'price' => '1500.00', 'discount' => '0.00', 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value],
            ['description' => 'ค่าหนังสือ', 'quantity' => 3, 'price' => '2900.00', 'discount' => '0.00', 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value],
            ['description' => 'ค่าลงทะเบียนแรกเข้า', 'quantity' => 1, 'price' => '750.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::REGISTRATION_FEE->value],
            ['description' => 'ค่าอุปกรณ์การเรียน', 'quantity' => 1, 'price' => '500.00', 'discount' => '0.00', 'tax' => 1, 'line_type' => ReceiptLineType::EQUIPMENT->value],
        ], $this->SummarizeDetails($receipt));
    }

    public function test_paid_order_generation_preserves_affiliate_commission_and_source_items(): void
    {
        [$department, $contact, $order] = $this->CreatePriceGroupOrder('0.00');
        [$profile, $attribution] = $this->AttachAffiliateAttribution($contact, $order);
        $source_rows = OrderDetailList::query()->where('order_id', $order->id)->orderBy('id')->get();
        $source_ids = $source_rows->pluck('id')->all();
        $actor = $this->CreateOrderEditor($department);

        $response = $this->actingAs($actor)->put(route('orders.save', $order->id), [
            'order_number' => 'ORD-AFF-PRICE-GRP',
            'cid' => $contact->id,
            'ref' => null,
            'courses_pending_id' => null,
            'total_cost' => '30950.00',
            'detail' => 'Affiliate price group regression fixture',
            'refund' => '0.00',
            'register_fee' => '750.00',
            'access_fee' => '500.00',
            'orther_fee' => '0.00',
            'discount' => '0.00',
            'payment' => 1,
            'discount_book' => '0.00',
            'status' => 1,
        ]);

        $response->assertOk()->assertJson([
            'success' => 'Save Data completed',
            'status' => 1,
        ]);
        $receipt = Receipt::query()->where('oid', $order->id)->sole();
        $commission = AffiliateCommission::query()->where('receipt_id', $receipt->id)->sole();

        $this->assertSame($profile->id, $commission->affiliate_profile_id);
        $this->assertSame($attribution->id, $commission->affiliate_attribution_id);
        $this->assertSame(1800000, $commission->eligible_tuition_minor);
        $this->assertSame(1500, $commission->rate_basis_points);
        $this->assertSame(270000, $commission->commission_minor);
        $this->assertSame(AffiliateCommissionStatus::PENDING, $commission->status);
        $this->assertSame($source_ids, $commission->SourceItems()->orderBy('order_detail_list_id')->pluck('order_detail_list_id')->all());
        $this->assertSame([300000, 400000, 400000, 300000, 400000], $commission->SourceItems()->orderBy('order_detail_list_id')->pluck('tuition_minor')->all());
        $this->assertSame('30950.00', $this->NormalizeDecimal((string) $order->fresh()->total_price));
        $this->assertSame('30950.00', $this->NormalizeDecimal((string) $receipt->fresh()->total_fee));
    }

    private function CreatePriceGroupOrder(string $total_price): array
    {
        $department = Department::create(['code' => 'DPR', 'name' => 'Demo Price Regression', 'status' => 1]);
        $contact = Contact::create([
            'centre' => $department->id,
            'code' => 'STU-PRICE-GROUP',
            'name' => 'Price Group Student',
            'level_name' => 'K1',
        ]);
        $order = Order::create([
            'centre' => $department->id,
            'cid' => $contact->id,
            'order_number' => 'ORD-PRICE-GROUP',
            'status' => 0,
            'payment' => 0,
            'total_price' => $total_price,
            'refund' => '0.00',
            'register_fee' => '750.00',
            'access_fee' => '500.00',
            'orther_fee' => '0.00',
            'discount' => '0.00',
            'discount_book' => '0.00',
        ]);

        foreach ([
            ['level' => 'K1', 'price' => '3000.00', 'bprice' => '1500.00'],
            ['level' => 'P6', 'price' => '4000.00', 'bprice' => '2900.00'],
            ['level' => 'P6', 'price' => '4000.00', 'bprice' => '2900.00'],
            ['level' => 'K1', 'price' => '3000.00', 'bprice' => '1500.00'],
            ['level' => 'P6', 'price' => '4000.00', 'bprice' => '2900.00'],
        ] as $source_row) {
            OrderDetailList::create(array_merge($source_row, ['order_id' => $order->id]));
        }

        return [$department, $contact, $order];
    }

    private function CreateOrderEditor(Department $department): User
    {
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        $actor->givePermissionTo(Permission::findOrCreate('order-edit', 'web'));

        return $actor;
    }

    private function AttachAffiliateAttribution(Contact $contact, Order $order): array
    {
        $affiliate_user = User::factory()->create(['password' => Hash::make('affiliate-secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $affiliate_user->id,
            'referral_code' => 'AFF-PRICE-GROUP-' . $affiliate_user->id,
            'status' => 'ACTIVE',
        ]);
        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now()->subDay(),
            'expires_at' => now()->addDays(179),
            'source_context' => 'course_referral',
        ]);
        $contact->forceFill(['current_affiliate_attribution_id' => $attribution->id])->save();
        $order->forceFill([
            'affiliate_attribution_id' => $attribution->id,
            'affiliate_commission_rate_basis_points' => 1500,
        ])->save();

        return [$profile, $attribution];
    }

    private function SummarizeDetails(Receipt $receipt): array
    {
        return receipt_details::query()
            ->where('receipt_id', $receipt->id)
            ->orderBy('id')
            ->get()
            ->map(fn (receipt_details $detail): array => [
                'description' => $detail->des,
                'quantity' => (int) $detail->quantity,
                'price' => $this->NormalizeDecimal((string) $detail->price),
                'discount' => $this->NormalizeDecimal((string) $detail->discount),
                'tax' => (int) $detail->tax,
                'line_type' => $detail->line_type?->value,
            ])
            ->all();
    }

    private function NormalizeDecimal(string $amount): string
    {
        [$whole_part, $fraction_part] = array_pad(explode('.', trim($amount), 2), 2, '');
        $whole_part = ltrim($whole_part, '0');
        $whole_part = $whole_part === '' ? '0' : $whole_part;

        return $whole_part . '.' . str_pad(substr($fraction_part, 0, 2), 2, '0');
    }
}
