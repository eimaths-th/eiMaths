<?php

namespace App\Http\Requests\BookShipments;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BookShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        return $actor !== null && (int) $actor->disable === 0;
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'VALIDATION_FAILED',
                'message' => 'The request data is invalid.',
                'details' => $validator->errors()->toArray(),
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    protected function AddEligibleDestinationValidation(
        Validator $validator,
        string $field = 'destination_centre_id'
    ): void {
        $validator->after(function (Validator $destination_validator) use ($field): void {
            $destination_centre_id = $this->input($field);
            if (!is_numeric($destination_centre_id) || (int) $destination_centre_id < 1) {
                return;
            }

            $is_eligible = Department::query()
                ->whereKey((int) $destination_centre_id)
                ->where('status', 1)
                ->whereKeyNot(Department::HQ_ID)
                ->exists();

            if (!$is_eligible) {
                $destination_validator->errors()->add(
                    $field,
                    'The destination Centre is not eligible.'
                );
            }
        });
    }
}
