<?php

namespace App\Support\Branch;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

final class BranchNumber
{
    public static function next(string $model, string $branchId, string $column, string $prefix, int $width, bool $monthly, bool $preview): string
    {
        return DB::transaction(function () use ($model, $branchId, $column, $prefix, $width, $monthly, $preview) {
            $branch = Department::query()->lockForUpdate()->findOrFail($branchId);
            abort_unless((int) $branch->status === 1 && trim((string) $branch->code) !== '', 422, 'An active branch with a code is required.');
            $type = strtoupper(trim($branch->code));
            $key = [$column => $type, 'year' => date('Y')];
            if ($monthly) { $key['month'] = date('m'); }
            // Student/product codes do not contain a year, so they must never reset annually.
            $query = $model::where($column, $type);
            if ($monthly) { $query->where('year', $key['year'])->where('month', $key['month']); }
            $counter_records = $query->lockForUpdate()->get();
            $number = (int) $counter_records->max('number') + 1;
            $issued_record = match ($model) {
                \App\Models\studentRunningNumber::class => ['contacts', 'code'],
                \App\Models\ProductRunningNumber::class => ['products', 'code'],
                \App\Models\OrderRunningNumber::class => ['orders', 'order_number'],
                \App\Models\ReceiptRunningNumber::class => ['receipts', 'receipt_number'],
                \App\Models\InvoiceRunningNumber::class => ['invoices', 'invoice_number'],
                default => null,
            };
            $number_prefix = $prefix.$type.($monthly ? date('ym') : '');
            $issued_numbers = [];
            if ($issued_record) {
                $number_pattern = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $number_prefix).'%';
                foreach (DB::table($issued_record[0])
                    ->whereRaw($issued_record[1]." LIKE ? ESCAPE '!'", [$number_pattern])
                    ->lockForUpdate()
                    ->pluck($issued_record[1]) as $existing_number) {
                    $issued_numbers[strtoupper(rtrim((string) $existing_number, ' '))] = true;
                }
            }
            do {
                $issued_number = $number_prefix.str_pad((string) $number, $width, '0', STR_PAD_LEFT);
                $already_issued = isset($issued_numbers[strtoupper($issued_number)]);
                if ($already_issued) { $number++; }
            } while ($already_issued);
            if (!$preview) {
                $current_counter = $counter_records->first(function ($counter_record) use ($key, $column): bool {
                    foreach ($key as $key_column => $key_value) {
                        $counter_value = (string) $counter_record->getAttribute($key_column);
                        if ($key_column === $column) {
                            if (strcasecmp($counter_value, (string) $key_value) !== 0) { return false; }
                        } elseif ($counter_value !== (string) $key_value) {
                            return false;
                        }
                    }
                    return true;
                });
                if ($current_counter) {
                    $current_counter->update(['number' => $number]);
                } else {
                    $model::create($key + ['number' => $number]);
                }
            }
            return $issued_number;
        }, 3);
    }
}
