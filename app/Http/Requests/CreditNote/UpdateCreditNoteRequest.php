<?php

namespace App\Http\Requests\CreditNote;

use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCreditNoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'lock_version' => ['required', 'integer', 'min:0'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_address' => ['nullable', 'string', 'max:2000'],
            'buyer_tax_id' => ['required_if:buyer_is_vat_registered,1', 'nullable', 'string', 'max:32'],
            'buyer_is_vat_registered' => ['required', 'boolean'],
            'buyer_branch_type' => ['required_if:buyer_is_vat_registered,1', 'nullable', 'in:head_office,branch'],
            'buyer_branch_code' => ['required_if:buyer_is_vat_registered,1', 'nullable', 'regex:/\A[0-9]{5}\z/D'],
            'reason_code' => ['required', 'in:' . implode(',', array_column(CreditNoteReasonCode::cases(), 'value'))],
            'reason_details' => ['required_if:reason_code,other', 'nullable', 'string', 'max:2000'],
            'adjustment_event_date' => ['required', 'date_format:Y-m-d'],
            'late_issue_reason' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.source_type' => ['required', 'in:receipt_detail,receipt_service_charge'],
            'items.*.source_id' => ['required', 'integer', 'min:1'],
            'items.*.credit_quantity' => ['nullable', 'regex:/\A[0-9]+(?:\.[0-9]{1,2})?\z/D'],
            'items.*.credit_amount' => ['nullable', 'regex:/\A[0-9]+(?:\.[0-9]{1,2})?\z/D'],
        ];
    }
}
