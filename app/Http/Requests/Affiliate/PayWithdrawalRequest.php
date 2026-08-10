<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class PayWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'transition_key' => ['required', 'string', 'max:120'],
            'payment_reference' => ['required', 'string', 'max:255'],
        ];
    }
}
