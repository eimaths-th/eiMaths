<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class AffiliateAnalyticsRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d'],
            'sales_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                $this->Range();
            } catch (InvalidArgumentException $exception) {
                $validator->errors()->add('to', $this->TranslateRangeError($exception));
            }
        });
    }

    public function Range(): AffiliateAnalyticsRange
    {
        return AffiliateAnalyticsRange::FromStrings(
            (string) $this->validated('from'),
            (string) $this->validated('to'),
        );
    }

    public function SalesPage(): int
    {
        return max(1, (int) ($this->validated('sales_page') ?? 1));
    }

    private function TranslateRangeError(InvalidArgumentException $exception): string
    {
        return match ($exception->getMessage()) {
            'Affiliate analytics dates must use the Y-m-d format.' => __('affiliate.validation.analytics_invalid_date'),
            'Affiliate analytics dates cannot be in the future.' => __('affiliate.validation.analytics_future_date'),
            'Affiliate analytics start date must not be after the end date.' => __('affiliate.validation.analytics_reversed_range'),
            'Affiliate analytics date ranges cannot exceed 366 days.' => __('affiliate.validation.analytics_range_too_long'),
            default => __('affiliate.validation.analytics_range'),
        };
    }
}
