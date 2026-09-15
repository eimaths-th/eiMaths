<?php

namespace App\Http\Requests\Affiliate;

use App\Domain\Affiliate\Services\AffiliateDestinationValidator;
use App\Models\AffiliateContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAffiliateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('affiliate-content-manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('publish_indefinitely')) {
            $this->merge(['publish_ends_at' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'campaign_code' => ['prohibited'],
            'file' => ['sometimes', 'file', 'max:51200'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'required', 'in:enabled,disabled'],
            'destination_target' => ['sometimes', 'required', 'string', 'max:2048'],
            'publish_indefinitely' => ['sometimes', 'boolean'],
            'publish_starts_at' => ['nullable', 'date'],
            'publish_ends_at' => ['nullable', 'date', 'after:publish_starts_at'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('destination_target')) {
                try {
                    app(AffiliateDestinationValidator::class)->Normalize((string) $this->input('destination_target'));
                } catch (\Throwable $exception) {
                    $validator->errors()->add('destination_target', $exception->getMessage());
                }
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

        $content = $this->route('affiliate_content');
        if (! $content instanceof AffiliateContent) {
            $validator->errors()->add('file', 'The Affiliate media record could not be resolved.');
            return;
        }

        $type = (string) $content->type;
        $allowed_mime_types = $type === 'image'
            ? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            : ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
        $allowed_extensions = $type === 'image'
            ? ['jpg', 'jpeg', 'png', 'gif', 'webp']
            : ['mp4', 'mov', 'avi', 'wmv'];

        if (! in_array(strtolower((string) $file->getMimeType()), $allowed_mime_types, true)) {
            $validator->errors()->add('file', 'The replacement file MIME type must match the existing media type.');
        }
        if (! in_array(strtolower((string) $file->getClientOriginalExtension()), $allowed_extensions, true)) {
            $validator->errors()->add('file', 'The replacement file extension must match the existing media type.');
        }
    }
}
