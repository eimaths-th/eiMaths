<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class CreateWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'string', 'regex:/\\A[0-9]+(?:\\.[0-9]{1,2})?\\z/'],
            'payout_account_id' => ['required', 'integer', 'min:1'],
            'request_key' => ['required', 'string', 'max:120'],
        ];
    }
}
