<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Models\CreditNoteRunningNumber;
use App\Models\Department;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class CreditNoteNumberGenerator
{
    public function Next(int $centre_id, CarbonImmutable $issue_date): string
    {
        $department = Department::query()->find($centre_id);
        $centre_code = strtoupper(trim((string) ($department?->code ?? '')));
        if ($centre_code === '' || !preg_match('/\A[A-Z0-9-]{1,16}\z/D', $centre_code)) {
            throw new \InvalidArgumentException('Credit Note centre code is missing or unsafe.');
        }

        $year = (int) $issue_date->year;
        $month = (int) $issue_date->month;
        $sequence = CreditNoteRunningNumber::query()
            ->where('centre_id', $centre_id)
            ->where('year', $year)
            ->where('month', $month)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            try {
                $sequence = CreditNoteRunningNumber::create([
                    'centre_id' => $centre_id,
                    'year' => $year,
                    'month' => $month,
                    'number' => 0,
                ]);
            } catch (QueryException $exception) {
                if (!$this->IsUniqueConflict($exception)) {
                    throw $exception;
                }
                $sequence = CreditNoteRunningNumber::query()
                    ->where('centre_id', $centre_id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->lockForUpdate()
                    ->firstOrFail();
            }
        }

        $sequence->number++;
        $sequence->save();

        return $centre_code . 'CN' . substr((string) $year, -2) . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . str_pad((string) $sequence->number, 3, '0', STR_PAD_LEFT);
    }

    private function IsUniqueConflict(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'unique') || str_contains($message, 'constraint');
    }
}
