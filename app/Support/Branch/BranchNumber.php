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
            $number = (int) $query->max('number') + 1;
            if (!$preview) { $model::updateOrCreate($key, ['number' => $number]); }
            return $prefix.$type.($monthly ? date('ym') : '').str_pad((string) $number, $width, '0', STR_PAD_LEFT);
        }, 3);
    }
}
