<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmedRefundRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'refunded_tuition_amount' => ['required', 'string', 'regex:/\\A[0-9]+(?:\\.[0-9]{1,2})?\\z/'],
            'external_refund_reference' => ['required', 'string', 'max:160'],
            'reason' => ['required', 'string', 'max:500'],
            'idempotency_key' => ['required', 'string', 'max:120'],
        ];
    }
}
