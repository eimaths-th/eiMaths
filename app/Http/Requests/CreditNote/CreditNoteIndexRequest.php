<?php

namespace App\Http\Requests\CreditNote;

use Illuminate\Foundation\Http\FormRequest;

class CreditNoteIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:80'],
            'original_number' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:draft,issued,cancelled'],
            'reason_code' => ['nullable', 'string', 'max:80'],
            'centre_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
