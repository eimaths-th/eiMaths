<?php

namespace App\Http\Requests\BookShipments;

class UpdateBookShipmentRequest extends BookShipmentRequest
{
    public function rules(): array
    {
        return [
            'destination_centre_id' => ['prohibited'],
            'source_book_ids' => ['required', 'array', 'min:1'],
            'source_book_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ];
    }
}
