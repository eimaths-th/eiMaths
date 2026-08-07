<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class AffiliateAdjustmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'available_amount' => ['required', 'string', 'regex:/\\A-?[0-9]+(?:\\.[0-9]{1,2})?\\z/', 'not_regex:/\\A-?0+(?:\\.0{1,2})?\\z/'],
            'reason' => ['required', 'string', 'max:500'],
            'idempotency_key' => ['required', 'string', 'max:120'],
        ];
    }
}
