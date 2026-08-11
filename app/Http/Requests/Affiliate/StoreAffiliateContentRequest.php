<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\Services\AffiliateDestinationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class StoreAffiliateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('affiliate-content-manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in(['image', 'video'])],
            'file' => ['required', 'file', 'max:51200'],
            'destination_target' => ['required', 'string', 'max:2048'],
            'campaign_code' => ['required', 'string', 'max:96', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D', Rule::unique('affiliate_contents', 'campaign_code')],
            'publish_starts_at' => ['nullable', 'date'],
            'publish_ends_at' => ['nullable', 'date', 'after:publish_starts_at'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            try {
                app(AffiliateDestinationValidator::class)->Normalize((string) $this->input('destination_target'));
            } catch (\Throwable $exception) {
                $validator->errors()->add('destination_target', $exception->getMessage());
            }

            $this->ValidateObservedMedia($validator);
        });
    }

    private function ValidateObservedMedia(Validator $validator): void
    {
        $file = $this->file('file');
        if ($file === null) {
            return;
        }

        $type = (string) $this->input('type');
        $allowed = $type === 'image'
            ? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            : ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
        if (! in_array(strtolower((string) $file->getMimeType()), $allowed, true)) {
            $validator->errors()->add('file', 'The uploaded file MIME type does not match the selected media type.');
        }
    }
}
