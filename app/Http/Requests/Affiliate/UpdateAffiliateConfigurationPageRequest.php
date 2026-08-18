<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\ValueObjects\Money;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class UpdateAffiliateConfigurationPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('affiliate-config-edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'commission_rate_percent' => [
                'bail',
                'required',
                'string',
                'regex:/\A(?:100(?:\.0{1,2})?|(?:\d|[1-9]\d)(?:\.\d{1,2})?)\z/D',
            ],
            'commission_holding_days' => ['bail', 'required', 'integer', 'min:0', 'max:3650'],
            'attribution_duration_days' => ['bail', 'required', 'integer', 'min:1', 'max:3650'],
            'minimum_withdrawal_baht' => [
                'bail',
                'required',
                'string',
                'regex:/\A\d+(?:\.\d{1,2})?\z/D',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'commission_rate_percent.required' => __('affiliate.configuration.validation.commission_rate_percent'),
            'commission_rate_percent.string' => __('affiliate.configuration.validation.commission_rate_percent'),
            'commission_rate_percent.regex' => __('affiliate.configuration.validation.commission_rate_percent'),
            'commission_holding_days.required' => __('affiliate.configuration.validation.commission_holding_days'),
            'commission_holding_days.integer' => __('affiliate.configuration.validation.commission_holding_days'),
            'commission_holding_days.min' => __('affiliate.configuration.validation.commission_holding_days'),
            'commission_holding_days.max' => __('affiliate.configuration.validation.commission_holding_days'),
            'attribution_duration_days.required' => __('affiliate.configuration.validation.attribution_duration_days'),
            'attribution_duration_days.integer' => __('affiliate.configuration.validation.attribution_duration_days'),
            'attribution_duration_days.min' => __('affiliate.configuration.validation.attribution_duration_days'),
            'attribution_duration_days.max' => __('affiliate.configuration.validation.attribution_duration_days'),
            'minimum_withdrawal_baht.required' => __('affiliate.configuration.validation.minimum_withdrawal_baht'),
            'minimum_withdrawal_baht.string' => __('affiliate.configuration.validation.minimum_withdrawal_baht'),
            'minimum_withdrawal_baht.regex' => __('affiliate.configuration.validation.minimum_withdrawal_baht'),
        ];
    }

    public function attributes(): array
    {
        return [
            'commission_rate_percent' => __('affiliate.configuration.commission_rate'),
            'commission_holding_days' => __('affiliate.configuration.commission_holding_days'),
            'attribution_duration_days' => __('affiliate.configuration.attribution_duration'),
            'minimum_withdrawal_baht' => __('affiliate.configuration.minimum_withdrawal'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed_keys = array_merge(array_keys($this->rules()), ['_token']);
            foreach (array_diff(array_keys($this->all()), $allowed_keys) as $unknown_key) {
                $validator->errors()->add(
                    $unknown_key,
                    __('affiliate.configuration.validation.unknown_field'),
                );
            }

            if ($validator->errors()->has('minimum_withdrawal_baht')) {
                return;
            }

            try {
                $minimum_withdrawal = Money::FromDecimal(
                    (string) $this->input('minimum_withdrawal_baht'),
                    true,
                );
                if ($minimum_withdrawal->Minor() < 1) {
                    $validator->errors()->add(
                        'minimum_withdrawal_baht',
                        __('affiliate.configuration.validation.minimum_withdrawal_baht'),
                    );
                }
            } catch (InvalidArgumentException) {
                $validator->errors()->add(
                    'minimum_withdrawal_baht',
                    __('affiliate.configuration.validation.minimum_withdrawal_baht'),
                );
            }
        });
    }

    public function ApprovedSettings(): array
    {
        $minimum_withdrawal = Money::FromDecimal(
            (string) $this->validated('minimum_withdrawal_baht'),
            true,
        );

        return [
            'commission_rate_basis_points' => $this->CommissionRateBasisPoints(),
            'commission_holding_days' => (int) $this->validated('commission_holding_days'),
            'attribution_duration_days' => (int) $this->validated('attribution_duration_days'),
            'minimum_withdrawal_minor' => $minimum_withdrawal->Minor(),
        ];
    }

    private function CommissionRateBasisPoints(): int
    {
        $value = (string) $this->validated('commission_rate_percent');
        preg_match(
            '/\A(\d+)(?:\.(\d{1,2}))?\z/D',
            $value,
            $matches,
        );

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }
}
