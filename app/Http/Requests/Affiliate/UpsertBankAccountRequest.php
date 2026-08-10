<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class UpsertBankAccountRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:32'],
            'account_name' => ['required', 'string', 'max:255'],
            'current_password' => ['required', 'string', 'max:255'],
        ];
    }
}
