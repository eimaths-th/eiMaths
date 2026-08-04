<?php

namespace App\Http\Requests\BookShipments;

use Illuminate\Validation\Validator;

class CandidateBookShipmentRequest extends BookShipmentRequest
{
    public function rules(): array
    {
        return [
            'destination_centre_id' => ['required', 'integer', 'min:1'],
            'draft_shipment_id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->AddEligibleDestinationValidation($validator);
    }
}
