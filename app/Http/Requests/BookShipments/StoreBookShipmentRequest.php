<?php

namespace App\Http\Requests\BookShipments;

use Illuminate\Validation\Validator;

class StoreBookShipmentRequest extends BookShipmentRequest
{
    public function rules(): array
    {
        return [
            'destination_centre_id' => ['required', 'integer', 'min:1'],
            'source_book_ids' => ['required', 'array', 'min:1'],
            'source_book_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->AddEligibleDestinationValidation($validator);
    }
}
