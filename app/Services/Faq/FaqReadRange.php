<?php
namespace App\Services\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
final class FaqReadRange
{
    public function __construct(public readonly string $range, public readonly string $start_date,
        public readonly string $end_date, public readonly int $page) {}
    public static function From(array $parameters): self
    {
        $today = CarbonImmutable::now('Asia/Bangkok')->startOfDay();
        $validated = Validator::make($parameters, [
            'range' => ['sometimes', 'required', 'in:7d,30d,custom'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
        ])->validate();
        $range = $validated['range'] ?? '30d';
        $page = (int) ($validated['page'] ?? 1);
        if ($range !== 'custom') {
            return new self($range, $today->subDays($range === '7d' ? 6 : 29)->toDateString(), $today->toDateString(), $page);
        }
        $dates = Validator::make($parameters, [
            'start_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today->toDateString()],
        ])->validate();
        return new self($range, $dates['start_date'], $dates['end_date'], $page);
    }
}
