<?php

namespace App\Domain\Financial\Services;

use App\Domain\Financial\Exceptions\ReceiptDetailCalculationException;
use App\Domain\Financial\ValueObjects\ReceiptDetailDraft;
use App\Models\Order;
use App\Models\OrderDetailList;
use Illuminate\Support\Collection;

final class ReceiptDetailBuilder
{
    public function BuildForOrder(Order $order): Collection
    {
        $order_id = (int) $order->getKey();
        $source_rows = $this->SourceRows($order);

        if ($source_rows->isEmpty()) {
            throw $this->CalculationFailure($order_id, 'no source detail rows exist');
        }

        $tuition_groups = [];
        $book_groups = [];
        $course_level = null;

        foreach ($source_rows as $source_row) {
            if (!$source_row instanceof OrderDetailList) {
                throw $this->CalculationFailure($order_id, 'a source detail row has an invalid model type');
            }

            if ($course_level === null) {
                $course_level = (string) ($source_row->level ?? '');
            }

            $tuition_price_minor = $this->ParseRequiredMinor(
                $source_row->price,
                $order_id,
                'tuition price',
            );
            $book_price_minor = $this->ParseRequiredMinor(
                $source_row->bprice,
                $order_id,
                'book price',
            );

            $this->AddToGroup($tuition_groups, $tuition_price_minor);
            $this->AddToGroup($book_groups, $book_price_minor);
        }

        $tuition_discount_minor = $this->ParseOptionalMinor($order->discount, $order_id, 'tuition discount');
        $book_discount_minor = $this->ParseOptionalMinor($order->discount_book, $order_id, 'book discount');
        $drafts = new Collection();

        $tuition_allocations = $this->AllocateDiscounts(
            $tuition_groups,
            $tuition_discount_minor,
            $order_id,
            'tuition',
        );
        foreach ($tuition_groups as $group_key => $group) {
            $drafts->push(new ReceiptDetailDraft(
                description: 'ค่าคอร์สเรียน ' . $this->CourseDescription($course_level),
                unit: '',
                quantity: $group['quantity'],
                unit_price_minor: $group['unit_price_minor'],
                discount_minor: $tuition_allocations[$group_key],
                legacy_tax: 1,
            ));
        }

        $book_allocations = $this->AllocateDiscounts(
            $book_groups,
            $book_discount_minor,
            $order_id,
            'book',
        );
        foreach ($book_groups as $group_key => $group) {
            $drafts->push(new ReceiptDetailDraft(
                description: 'ค่าหนังสือ',
                unit: '',
                quantity: $group['quantity'],
                unit_price_minor: $group['unit_price_minor'],
                discount_minor: $book_allocations[$group_key],
                legacy_tax: 0,
            ));
        }

        $this->AppendFee($drafts, $order->refund, $order_id, 'ค่ามัดจำ');
        $this->AppendFee($drafts, $order->register_fee, $order_id, 'ค่าลงทะเบียนแรกเข้า');
        $this->AppendFee($drafts, $order->access_fee, $order_id, 'ค่าอุปกรณ์การเรียน');

        $other_fee_minor = $this->ParseOptionalMinor($order->orther_fee, $order_id, 'other fee');
        $stored_total_minor = $this->ParseRequiredMinor($order->total_price, $order_id, 'order total');
        $generated_total_minor = $drafts->sum(static fn (ReceiptDetailDraft $draft): int => $draft->NetMinor());

        if ($generated_total_minor > PHP_INT_MAX - $other_fee_minor
            || $generated_total_minor + $other_fee_minor !== $stored_total_minor) {
            throw $this->CalculationFailure($order_id, 'generated detail total does not reconcile with the stored order total');
        }

        return $drafts;
    }

    private function SourceRows(Order $order): Collection
    {
        if ($order->relationLoaded('orderDetailLists')) {
            return $order->getRelation('orderDetailLists')
                ->sortBy(static fn (OrderDetailList $source_row): int => (int) $source_row->getKey())
                ->values();
        }

        return $order->orderDetailLists()->orderBy('id')->get();
    }

    private function AddToGroup(array &$groups, int $unit_price_minor): void
    {
        if (!array_key_exists($unit_price_minor, $groups)) {
            $groups[$unit_price_minor] = [
                'unit_price_minor' => $unit_price_minor,
                'quantity' => 0,
            ];
        }

        $groups[$unit_price_minor]['quantity']++;
    }

    private function AllocateDiscounts(array $groups, int $discount_minor, int $order_id, string $category): array
    {
        $gross_total_minor = 0;
        $gross_by_group = [];
        foreach ($groups as $group_key => $group) {
            $gross_minor = $this->MultiplyMinor($group['unit_price_minor'], $group['quantity'], $order_id, $category . ' gross amount');
            $gross_by_group[$group_key] = $gross_minor;
            if ($gross_total_minor > PHP_INT_MAX - $gross_minor) {
                throw $this->CalculationFailure($order_id, $category . ' gross total exceeds the supported range');
            }
            $gross_total_minor += $gross_minor;
        }

        if ($discount_minor > $gross_total_minor) {
            throw $this->CalculationFailure($order_id, $category . ' discount exceeds the gross amount');
        }

        $allocations = array_fill_keys(array_keys($groups), 0);
        if ($discount_minor === 0 || $gross_total_minor === 0) {
            return $allocations;
        }

        $remainders = [];
        $allocated_minor = 0;
        foreach ($gross_by_group as $group_key => $gross_minor) {
            $product_minor = $this->MultiplyMinor($discount_minor, $gross_minor, $order_id, $category . ' discount allocation');
            $allocations[$group_key] = intdiv($product_minor, $gross_total_minor);
            $remainders[$group_key] = $product_minor % $gross_total_minor;
            $allocated_minor += $allocations[$group_key];
        }

        $remaining_minor = $discount_minor - $allocated_minor;
        if ($remaining_minor > 0) {
            uksort($remainders, static function (int|string $left_key, int|string $right_key) use ($remainders): int {
                $remainder_comparison = $remainders[$right_key] <=> $remainders[$left_key];
                return $remainder_comparison !== 0
                    ? $remainder_comparison
                    : ((int) $left_key <=> (int) $right_key);
            });

            foreach (array_keys($remainders) as $group_key) {
                if ($remaining_minor === 0) {
                    break;
                }
                if ($allocations[$group_key] < $gross_by_group[$group_key]) {
                    $allocations[$group_key]++;
                    $remaining_minor--;
                }
            }
        }

        if ($remaining_minor !== 0) {
            throw $this->CalculationFailure($order_id, $category . ' discount could not be allocated');
        }

        return $allocations;
    }

    private function AppendFee(Collection $drafts, mixed $raw_amount, int $order_id, string $description): void
    {
        $amount_minor = $this->ParseOptionalMinor($raw_amount, $order_id, $description);
        if ($amount_minor === 0) {
            return;
        }

        $drafts->push(new ReceiptDetailDraft(
            description: $description,
            unit: '',
            quantity: 1,
            unit_price_minor: $amount_minor,
            discount_minor: 0,
            legacy_tax: 1,
        ));
    }

    private function CourseDescription(?string $course_level): string
    {
        return match (strtoupper(substr(trim((string) $course_level), 0, 1))) {
            'K' => 'Kindergarten',
            'P' => 'Primary school',
            default => strtoupper(substr(trim((string) $course_level), 0, 1)),
        };
    }

    private function ParseRequiredMinor(mixed $raw_amount, int $order_id, string $field_name): int
    {
        if ($raw_amount === null || $raw_amount === '' || (!is_int($raw_amount) && !is_string($raw_amount))) {
            throw $this->CalculationFailure($order_id, $field_name . ' is missing or invalid');
        }

        $amount = (string) $raw_amount;
        if (!preg_match('/\A(?:0|[0-9]+)(?:\.[0-9]{1,2})?\z/D', $amount)) {
            throw $this->CalculationFailure($order_id, $field_name . ' is invalid');
        }

        [$whole_part, $fraction_part] = array_pad(explode('.', $amount, 2), 2, '');
        $whole_part = ltrim($whole_part, '0');
        $whole_part = $whole_part === '' ? '0' : $whole_part;
        $fraction_part = str_pad($fraction_part, 2, '0');

        if (strlen($whole_part) > 16) {
            throw $this->CalculationFailure($order_id, $field_name . ' exceeds the supported range');
        }

        $whole_minor = (int) $whole_part;
        if ($whole_minor > intdiv(PHP_INT_MAX, 100)) {
            throw $this->CalculationFailure($order_id, $field_name . ' exceeds the supported range');
        }

        $minor_amount = $whole_minor * 100 + (int) $fraction_part;
        if ($minor_amount < 0) {
            throw $this->CalculationFailure($order_id, $field_name . ' is invalid');
        }

        return $minor_amount;
    }

    private function ParseOptionalMinor(mixed $raw_amount, int $order_id, string $field_name): int
    {
        if ($raw_amount === null || $raw_amount === '') {
            return 0;
        }

        return $this->ParseRequiredMinor($raw_amount, $order_id, $field_name);
    }

    private function MultiplyMinor(int $left, int $right, int $order_id, string $field_name): int
    {
        if ($left !== 0 && $right > intdiv(PHP_INT_MAX, $left)) {
            throw $this->CalculationFailure($order_id, $field_name . ' exceeds the supported range');
        }

        return $left * $right;
    }

    private function CalculationFailure(int $order_id, string $reason): ReceiptDetailCalculationException
    {
        return new ReceiptDetailCalculationException("Receipt detail calculation failed for order {$order_id}: {$reason}.");
    }
}
