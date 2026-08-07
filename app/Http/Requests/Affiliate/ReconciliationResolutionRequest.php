<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class ReconciliationResolutionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'resolution_note' => ['required', 'string', 'max:1000'],
            'adjustment_ledger_entry_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
