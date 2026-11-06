<input type="hidden" name="privacy_notice_version" value="{{ config('privacy.notice_version') }}">

<div style="margin-bottom: 20px; font-size: 14px; color: #475569;">
    <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px;">
        <input type="checkbox" id="{{ $consent_field_prefix }}-pdpa" name="pdpa" value="1" required @checked(old('pdpa')) style="width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; accent-color: #3b82f6; cursor: pointer;" />
        <label for="{{ $consent_field_prefix }}-pdpa" style="margin-bottom: 0; cursor: pointer;">
            {{ __('students.login.privacy_acknowledgement') }}
            <a href="{{ config('privacy.notice_url') }}" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">{{ __('students.login.privacy_notice_link') }}</a>
            <span aria-hidden="true">*</span>
        </label>
    </div>
    <div style="display: flex; align-items: flex-start; gap: 10px;">
        <input type="checkbox" id="{{ $consent_field_prefix }}-marketing-consent" name="marketing_consent" value="1" @checked(old('marketing_consent')) style="width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; accent-color: #3b82f6; cursor: pointer;" />
        <label for="{{ $consent_field_prefix }}-marketing-consent" style="margin-bottom: 0; cursor: pointer;">{{ __('students.login.marketing_choice') }}</label>
    </div>
</div>
