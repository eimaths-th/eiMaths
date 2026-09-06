<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\Models\AffiliateIdentityProfile;
use App\Rules\ThaiNationalId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAffiliateIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'national_id' => preg_replace('/\D+/', '', (string) $this->input('national_id')),
        ]);
    }

    public function rules(): array
    {
        $identity_id = $this->user()?->AffiliateProfile?->IdentityProfile?->id;
        return [
            'honorific' => ['required', Rule::in(['mr', 'mrs', 'miss'])],
            'legal_first_name' => ['required', 'string', 'max:100'],
            'legal_last_name' => ['required', 'string', 'max:100'],
            'national_id' => [
                'required',
                new ThaiNationalId,
                function (string $attribute, mixed $value, \Closure $fail) use ($identity_id): void {
                    $duplicate = AffiliateIdentityProfile::query()
                        ->where('national_id_hash', AffiliateIdentityProfile::SensitiveHash((string) $value))
                        ->when($identity_id, fn ($query) => $query->whereKeyNot($identity_id))
                        ->exists();
                    if ($duplicate) {
                        $fail(__('validation.unique', ['attribute' => __('affiliate.identity.national_id')]));
                    }
                },
            ],
            'card_expires_on' => ['required', 'date', 'after:today'],
            'address_line1' => ['required', 'string', 'max:255'],
            'subdistrict' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'regex:/^[0-9]{5}$/'],
            'id_document' => [
                $this->user()?->AffiliateProfile?->IdentityProfile?->id_document_path ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
            'identity_certified' => ['accepted'],
        ];
    }

}
