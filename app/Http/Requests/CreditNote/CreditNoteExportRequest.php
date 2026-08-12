<?php

namespace App\Http\Requests\CreditNote;

class CreditNoteExportRequest extends CreditNoteIndexRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'ids' => ['nullable', 'array', 'max:500'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }
}
