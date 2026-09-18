<?php

namespace App\Http\Requests\BookShipments;

use App\Models\BookShipmentItem;
use Illuminate\Validation\Validator;

class ReceiveBookShipmentRequest extends BookShipmentRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.shipment_item_id' => ['required', 'integer', 'distinct', 'min:1'],
            'items.*.centre_status' => [
                'required',
                'string',
                'in:' . implode(',', [
                    BookShipmentItem::CENTRE_STATUS_RECEIVED,
                    BookShipmentItem::CENTRE_STATUS_MISSING,
                    BookShipmentItem::CENTRE_STATUS_DAMAGED,
                ]),
            ],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $receive_validator): void {
            foreach ((array) $this->input('items', []) as $item_index => $item) {
                $centre_status = $item['centre_status'] ?? null;
                if (!in_array($centre_status, [
                    BookShipmentItem::CENTRE_STATUS_MISSING,
                    BookShipmentItem::CENTRE_STATUS_DAMAGED,
                ], true)) {
                    continue;
                }

                $note = $item['note'] ?? null;
                if (!is_string($note) || trim($note) === '') {
                    $receive_validator->errors()->add(
                        "items.{$item_index}.note",
                        trans('book-shipments.messages.missing_or_damaged_note_validation', [])
                    );
                }
            }
        });
    }
}
