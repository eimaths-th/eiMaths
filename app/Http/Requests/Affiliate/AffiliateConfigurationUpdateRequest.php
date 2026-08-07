<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class AffiliateConfigurationUpdateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'commission_rate_basis_points' => ['required', 'integer', 'min:0', 'max:10000'],
            'commission_holding_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'attribution_duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'minimum_withdrawal_minor' => ['required', 'integer', 'min:1'],
            'withdrawal_approval_required' => ['required', 'boolean'],
        ];
    }
}
