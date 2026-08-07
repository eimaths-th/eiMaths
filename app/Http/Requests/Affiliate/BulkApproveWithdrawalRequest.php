<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class BulkApproveWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'withdrawal_ids' => ['required', 'array', 'min:1', 'max:100'],
            'withdrawal_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
            'approval_key' => ['required', 'string', 'max:120'],
        ];
    }
}
