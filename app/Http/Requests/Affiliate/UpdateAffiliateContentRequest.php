<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\Services\AffiliateDestinationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAffiliateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('affiliate-content-manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'campaign_code' => ['prohibited'],
            'file' => ['prohibited'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'destination_target' => ['sometimes', 'required', 'string', 'max:2048'],
            'publish_starts_at' => ['nullable', 'date'],
            'publish_ends_at' => ['nullable', 'date', 'after:publish_starts_at'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('destination_target')) return;
            try {
                app(AffiliateDestinationValidator::class)->Normalize((string) $this->input('destination_target'));
            } catch (\Throwable $exception) {
                $validator->errors()->add('destination_target', $exception->getMessage());
            }
        });
    }
}
