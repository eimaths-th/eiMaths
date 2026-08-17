<?php

namespace App\Http\Requests\CreditNote;

use Illuminate\Foundation\Http\FormRequest;

class CancelCreditNoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $header_key = $this->header('X-Idempotency-Key');
        $form_key = $this->input('idempotency_key');
        if ($header_key !== null && $form_key !== null && (string) $header_key !== (string) $form_key) {
            $this->merge(['idempotency_key' => '__mismatch__']);
            return;
        }
        if ($header_key !== null) {
            $this->merge(['idempotency_key' => $header_key]);
        }
    }

    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:2000'],
            'idempotency_key' => ['required', 'regex:/\A[A-Za-z0-9._:-]{1,120}\z/D'],
        ];
    }
}
