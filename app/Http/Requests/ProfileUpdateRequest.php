<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'current_password' => [
                Rule::requiredIf(fn () => $this->input('email') !== $this->user()->email),
                'nullable', 'string', 'current_password:web',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('profile.validation.name_required'),
            'name.max' => __('profile.validation.name_max'),
            'email.required' => __('profile.validation.email_required'),
            'email.email' => __('profile.validation.email_invalid'),
            'email.max' => __('profile.validation.email_max'),
            'email.unique' => __('profile.validation.email_unique'),
            'current_password.required' => __('profile.validation.current_password_required'),
            'current_password.current_password' => __('profile.validation.current_password_invalid'),
        ];
    }
}
