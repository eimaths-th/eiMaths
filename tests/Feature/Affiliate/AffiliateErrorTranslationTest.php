<?php

namespace Tests\Feature\Affiliate;

use Tests\TestCase;

class AffiliateErrorTranslationTest extends TestCase
{
    public function test_affiliate_payout_errors_have_user_facing_translations_in_english(): void
    {
        $this->assertTranslation(
            'en',
            'affiliate.errors.password_confirmation_required',
            'Confirm the current password before changing payout account details.',
        );
        $this->assertTranslation(
            'en',
            'affiliate.errors.payout_account_cooldown',
            'Withdrawals are temporarily blocked after a payout account change.',
        );
    }

    public function test_affiliate_payout_errors_have_user_facing_translations_in_thai(): void
    {
        $this->assertTranslation(
            'th',
            'affiliate.errors.password_confirmation_required',
            'กรุณายืนยันรหัสผ่านปัจจุบันก่อนเปลี่ยนรายละเอียดบัญชีรับเงิน',
        );
        $this->assertTranslation(
            'th',
            'affiliate.errors.payout_account_cooldown',
            'ไม่สามารถถอนเงินได้ชั่วคราวหลังจากเปลี่ยนบัญชีรับเงิน',
        );
    }

    private function assertTranslation(string $locale, string $key, string $expected): void
    {
        $previous_locale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $translated = __($key);

            $this->assertNotSame($key, $translated);
            $this->assertSame($expected, $translated);
        } finally {
            app()->setLocale($previous_locale);
        }
    }
}
