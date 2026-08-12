<?php

namespace Tests\Unit\Financial;

use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\Financial\Exceptions\ReceiptDetailCalculationException;
use App\Domain\Financial\Services\ReceiptDetailBuilder;
use App\Models\Order;
use App\Models\OrderDetailList;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Collection;

class ReceiptDetailBuilderTest extends TestCase
{
    #[Test]
    public function BuildForOrderCreatesSeparatePriceGroupsInFirstOccurrenceOrder(): void
    {
        $order = $this->CreateOrder(
            [
                ['id' => 5, 'price' => '4000.00', 'bprice' => '2900.00', 'level' => 'P6'],
                ['id' => 1, 'price' => '3000.00', 'bprice' => '1500.00', 'level' => 'K1'],
                ['id' => 4, 'price' => '3000.00', 'bprice' => '1500.00', 'level' => 'K1'],
                ['id' => 2, 'price' => '4000.00', 'bprice' => '2900.00', 'level' => 'P6'],
                ['id' => 3, 'price' => '4000.00', 'bprice' => '2900.00', 'level' => 'P6'],
            ],
            [
                'total_price' => '30950.00',
                'register_fee' => '750.00',
                'access_fee' => '500.00',
            ],
        );

        $drafts = (new ReceiptDetailBuilder())->BuildForOrder($order);

        $this->assertSame([
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 2, 'price_minor' => 300000, 'discount_minor' => 0, 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value, 'net_minor' => 600000],
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 3, 'price_minor' => 400000, 'discount_minor' => 0, 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value, 'net_minor' => 1200000],
            ['description' => 'ค่าหนังสือ', 'quantity' => 2, 'price_minor' => 150000, 'discount_minor' => 0, 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value, 'net_minor' => 300000],
            ['description' => 'ค่าหนังสือ', 'quantity' => 3, 'price_minor' => 290000, 'discount_minor' => 0, 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value, 'net_minor' => 870000],
            ['description' => 'ค่าลงทะเบียนแรกเข้า', 'quantity' => 1, 'price_minor' => 75000, 'discount_minor' => 0, 'tax' => 1, 'line_type' => ReceiptLineType::REGISTRATION_FEE->value, 'net_minor' => 75000],
            ['description' => 'ค่าอุปกรณ์การเรียน', 'quantity' => 1, 'price_minor' => 50000, 'discount_minor' => 0, 'tax' => 1, 'line_type' => ReceiptLineType::EQUIPMENT->value, 'net_minor' => 50000],
        ], $this->SummarizeDrafts($drafts));
    }

    #[Test]
    public function BuildForOrderPreservesSinglePriceGroupSemantics(): void
    {
        $order = $this->CreateOrder(
            [
                ['id' => 1, 'price' => '4000.00', 'bprice' => '2900.00', 'level' => 'K1'],
                ['id' => 2, 'price' => '4000.00', 'bprice' => '2900.00', 'level' => 'K1'],
            ],
            ['total_price' => '13800.00'],
        );

        $drafts = (new ReceiptDetailBuilder())->BuildForOrder($order);

        $this->assertSame([
            ['description' => 'ค่าคอร์สเรียน Kindergarten', 'quantity' => 2, 'price_minor' => 400000, 'discount_minor' => 0, 'tax' => 1, 'line_type' => ReceiptLineType::TUITION->value, 'net_minor' => 800000],
            ['description' => 'ค่าหนังสือ', 'quantity' => 2, 'price_minor' => 290000, 'discount_minor' => 0, 'tax' => 0, 'line_type' => ReceiptLineType::BOOK->value, 'net_minor' => 580000],
        ], $this->SummarizeDrafts($drafts));
    }

    #[Test]
    public function BuildForOrderAllocatesDiscountsProportionallyWithDeterministicResiduals(): void
    {
        $order = $this->CreateOrder(
            [
                ['id' => 1, 'price' => '100.00', 'bprice' => '10.00', 'level' => 'K1'],
                ['id' => 2, 'price' => '200.00', 'bprice' => '20.00', 'level' => 'K1'],
                ['id' => 3, 'price' => '300.00', 'bprice' => '30.00', 'level' => 'K1'],
            ],
            [
                'discount' => '100.00',
                'discount_book' => '5.00',
                'total_price' => '555.00',
            ],
        );

        $drafts = (new ReceiptDetailBuilder())->BuildForOrder($order);

        $this->assertSame([1667, 3333, 5000], $drafts->take(3)->pluck('discount_minor')->all());
        $this->assertSame(10000, $drafts->take(3)->sum('discount_minor'));
        $this->assertSame([83, 167, 250], $drafts->slice(3)->pluck('discount_minor')->all());
        $this->assertSame(500, $drafts->slice(3)->sum('discount_minor'));
        $this->assertSame(55500, $drafts->sum(fn ($draft) => $draft->NetMinor()));
    }

    #[Test]
    public function BuildForOrderFailsExplicitlyForInvalidSourceOrReconciliationData(): void
    {
        $cases = [
            'no source rows' => $this->CreateOrder([], ['total_price' => '0.00']),
            'negative tuition price' => $this->CreateOrder([['id' => 1, 'price' => '-1.00', 'bprice' => '0.00', 'level' => 'K1']], ['total_price' => '0.00']),
            'invalid book price' => $this->CreateOrder([['id' => 1, 'price' => '1.00', 'bprice' => 'invalid', 'level' => 'K1']], ['total_price' => '1.00']),
            'discount greater than gross' => $this->CreateOrder([['id' => 1, 'price' => '10.00', 'bprice' => '1.00', 'level' => 'K1']], ['discount' => '10.01', 'total_price' => '1.00']),
            'stored total mismatch' => $this->CreateOrder([['id' => 1, 'price' => '10.00', 'bprice' => '1.00', 'level' => 'K1']], ['total_price' => '12.00']),
        ];

        foreach ($cases as $case_name => $order) {
            try {
                (new ReceiptDetailBuilder())->BuildForOrder($order);
                $this->fail("Expected {$case_name} to throw a calculation exception.");
            } catch (ReceiptDetailCalculationException $exception) {
                $this->assertNotSame('', $exception->getMessage(), $case_name);
            }
        }
    }

    private function CreateOrder(array $source_rows, array $attributes = []): Order
    {
        $order = new Order(array_merge([
            'total_price' => '0.00',
            'refund' => '0.00',
            'register_fee' => '0.00',
            'access_fee' => '0.00',
            'orther_fee' => '0.00',
            'discount' => '0.00',
            'discount_book' => '0.00',
        ], $attributes));
        $order->forceFill(['id' => 558]);
        $order->setRelation('orderDetailLists', new Collection(array_map(static function (array $source_row): OrderDetailList {
            $source_id = $source_row['id'];
            unset($source_row['id']);
            $source_detail = OrderDetailList::make($source_row);
            $source_detail->forceFill(['id' => $source_id]);

            return $source_detail;
        }, $source_rows)));

        return $order;
    }

    private function SummarizeDrafts(Collection $drafts): array
    {
        return $drafts->map(static fn ($draft): array => [
            'description' => $draft->description,
            'quantity' => $draft->quantity,
            'price_minor' => $draft->unit_price_minor,
            'discount_minor' => $draft->discount_minor,
            'tax' => $draft->legacy_tax,
            'line_type' => $draft->line_type->value,
            'net_minor' => $draft->NetMinor(),
        ])->all();
    }
}
