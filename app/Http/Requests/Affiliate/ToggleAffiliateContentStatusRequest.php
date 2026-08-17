<?php

namespace App\Http\Requests\Affiliate;

use App\Models\AffiliateContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleAffiliateContentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('affiliate-content-manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('affiliate_contents', 'id')->where(static function ($query): void {
                    $query->whereNull('deleted_at')->whereNull('archived_at');
                }),
            ],
        ];
    }
}
