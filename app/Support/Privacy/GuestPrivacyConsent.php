<?php

namespace App\Support\Privacy;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuestPrivacyConsent
{
    public static function Rules(): array
    {
        return [
            'pdpa' => ['accepted'],
            'privacy_notice_version' => ['required', 'string', Rule::in([config('privacy.notice_version')])],
            'marketing_consent' => ['nullable', Rule::in(['1'])],
        ];
    }

    public static function Messages(): array
    {
        return [
            'pdpa.accepted' => __('students.login.validation.privacy_required'),
            'privacy_notice_version.required' => __('students.login.validation.privacy_version_stale'),
            'privacy_notice_version.in' => __('students.login.validation.privacy_version_stale'),
            'marketing_consent.in' => __('students.login.validation.marketing_invalid'),
        ];
    }

    public static function Attributes(Request $request): array
    {
        $recorded_at = now();

        return [
            'privacy_notice_version' => $request->input('privacy_notice_version'),
            'privacy_acknowledged_at' => $recorded_at,
            'marketing_consent' => $request->input('marketing_consent') === '1',
            'marketing_consent_version' => config('privacy.consent_version'),
            'marketing_consent_recorded_at' => $recorded_at,
        ];
    }
}
