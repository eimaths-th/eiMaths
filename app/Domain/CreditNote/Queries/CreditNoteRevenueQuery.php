<?php

namespace App\Domain\CreditNote\Queries;

use App\Domain\CreditNote\Enums\CreditNotePostingType;
use Illuminate\Support\Facades\DB;

final class CreditNoteRevenueQuery
{
    public function ByDate(string $from_date, string $to_date, ?int $centre_id = null): array
    {
        return $this->Build($from_date, $to_date, $centre_id, 'date');
    }

    public function ByMonth(string $from_date, string $to_date, ?int $centre_id = null): array
    {
        return $this->Build($from_date, $to_date, $centre_id, 'month');
    }

    public function ByYear(string $from_date, string $to_date, ?int $centre_id = null): array
    {
        return $this->Build($from_date, $to_date, $centre_id, 'year');
    }

    public function ByBranch(string $from_date, string $to_date): array
    {
        return $this->Build($from_date, $to_date, null, 'branch');
    }

    public function Totals(string $from_date, string $to_date, ?int $centre_id = null): array
    {
        $rows = $this->Build($from_date, $to_date, $centre_id, 'totals');
        return array_reduce($rows, static function (array $totals, array $row): array {
            foreach (['gross_minor', 'issued_minor', 'cancelled_minor', 'net_minor', 'credit_note_count', 'issued_count', 'cancelled_count', 'receipt_count'] as $key) {
                $totals[$key] = ($totals[$key] ?? 0) + (int) ($row[$key] ?? 0);
            }
            return $totals;
        }, [
            'gross_minor' => 0, 'issued_minor' => 0, 'cancelled_minor' => 0, 'net_minor' => 0,
            'credit_note_count' => 0, 'issued_count' => 0, 'cancelled_count' => 0, 'receipt_count' => 0,
        ]);
    }

    private function Build(string $from_date, string $to_date, ?int $centre_id, string $group): array
    {
        $period_expression = $this->PeriodExpression($group, 'effective_date');
        $posting_query = DB::table('credit_note_postings')
            ->whereBetween('effective_date', [$from_date, $to_date]);
        if ($centre_id !== null) {
            $posting_query->where('centre_id', $centre_id);
        }
        $posting_query->selectRaw($group === 'branch' ? 'centre_id AS period_key' : ($group === 'totals' ? "'total' AS period_key" : "$period_expression AS period_key"))
            ->selectRaw('COALESCE(SUM(CASE WHEN posting_type = ? THEN -grand_total_delta_minor ELSE 0 END), 0) AS issued_minor', [CreditNotePostingType::ISSUE->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN posting_type = ? THEN grand_total_delta_minor ELSE 0 END), 0) AS cancelled_minor', [CreditNotePostingType::CANCELLATION->value])
            ->selectRaw('COALESCE(SUM(grand_total_delta_minor), 0) AS net_minor')
            ->selectRaw('COUNT(DISTINCT credit_note_id) AS credit_note_count')
            ->selectRaw('COUNT(DISTINCT CASE WHEN posting_type = ? THEN credit_note_id END) AS issued_count', [CreditNotePostingType::ISSUE->value])
            ->selectRaw('COUNT(DISTINCT CASE WHEN posting_type = ? THEN credit_note_id END) AS cancelled_count', [CreditNotePostingType::CANCELLATION->value])
            ->groupBy('period_key');
        $posting_rows = $posting_query->get()->keyBy('period_key');

        $receipt_query = DB::table('receipts')
            ->whereNotNull('payment_date')
            ->whereBetween(DB::raw('DATE(payment_date)'), [$from_date, $to_date])
            ->where('status', 1);
        if ($centre_id !== null) {
            $receipt_query->where('centre', $centre_id);
        }
        $receipt_expression = $this->PeriodExpression($group, 'payment_date');
        $receipt_query->selectRaw($group === 'branch' ? 'centre AS period_key' : ($group === 'totals' ? "'total' AS period_key" : "$receipt_expression AS period_key"))
            ->selectRaw('COALESCE(SUM(CAST(ROUND(CAST(total_fee AS DECIMAL(18,2)) * 100, 0) AS INTEGER)), 0) AS gross_minor')
            ->selectRaw('COUNT(*) AS receipt_count')
            ->groupBy('period_key');
        $receipt_rows = $receipt_query->get()->keyBy('period_key');

        $keys = $posting_rows->keys()->merge($receipt_rows->keys())->unique()->sort()->values();
        $result = [];
        foreach ($keys as $key) {
            $posting = $posting_rows->get($key);
            $receipt = $receipt_rows->get($key);
            $result[(string) $key] = [
                'gross_minor' => (int) ($receipt->gross_minor ?? 0),
                'issued_minor' => (int) ($posting->issued_minor ?? 0),
                'cancelled_minor' => (int) ($posting->cancelled_minor ?? 0),
                'net_minor' => (int) ($posting->net_minor ?? 0),
                'credit_note_count' => (int) ($posting->credit_note_count ?? 0),
                'issued_count' => (int) ($posting->issued_count ?? 0),
                'cancelled_count' => (int) ($posting->cancelled_count ?? 0),
                'receipt_count' => (int) ($receipt->receipt_count ?? 0),
            ];
        }

        return $result;
    }

    private function PeriodExpression(string $group, string $column): string
    {
        return match ($group) {
            'month' => DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', $column)"
                : "DATE_FORMAT($column, '%Y-%m')",
            'year' => DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y', $column)"
                : "YEAR($column)",
            default => "DATE($column)",
        };
    }
}
