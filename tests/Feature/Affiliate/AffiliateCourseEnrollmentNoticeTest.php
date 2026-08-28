<?php

namespace Tests\Feature\Affiliate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateCourseEnrollmentNoticeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CourseEnrollmentModalShowsTheDirectPaymentNoticeBeforeParentInformation(): void
    {
        $previous_locale = app()->getLocale();
        app()->setLocale('th');

        try {
            $rendered_modal = view('affiliate.form_modal', [
                'ref' => 'AFF-NOTICE-001',
                'departments' => [],
                'days' => [],
            ])->render();
        } finally {
            app()->setLocale($previous_locale);
        }

        $notice_position = strpos($rendered_modal, '<aside class="affiliate-payment-notice"');
        $parent_information_position = strpos($rendered_modal, 'ข้อมูลผู้ปกครอง (Parent Information)');

        $this->assertNotFalse($notice_position);
        $this->assertNotFalse($parent_information_position);
        $this->assertLessThan($parent_information_position, $notice_position);
        $this->assertStringContainsString('แจ้งเตือนสำคัญเรื่องการชำระเงิน', $rendered_modal);
        $this->assertStringContainsString('บริษัทไม่มีนโยบายเรียกเก็บเงินจากตัวแทน Affiliate', $rendered_modal);
        $this->assertStringContainsString('การชำระค่าเรียนต้องชำระที่สาขา eiMaths โดยตรงเท่านั้น', $rendered_modal);
        $this->assertStringContainsString('กรุณาอย่าโอนเงินให้ตัวแทนหรือบุคคลอื่น', $rendered_modal);
    }

    #[Test]
    public function CourseEnrollmentModalTranslatesTheDirectPaymentNoticeToEnglish(): void
    {
        $previous_locale = app()->getLocale();
        app()->setLocale('en');

        try {
            $rendered_modal = view('affiliate.form_modal', [
                'ref' => 'AFF-NOTICE-002',
                'departments' => [],
                'days' => [],
            ])->render();
        } finally {
            app()->setLocale($previous_locale);
        }

        $this->assertStringContainsString('Important payment notice', $rendered_modal);
        $this->assertStringContainsString('The company does not charge Affiliate representatives any fees.', $rendered_modal);
        $this->assertStringContainsString('Course payments must be made directly at an eiMaths branch only.', $rendered_modal);
        $this->assertStringContainsString('Please do not transfer money to an Affiliate representative or any other person.', $rendered_modal);
    }

    #[Test]
    public function CoursesRouteUsesThaiLocaleForPaymentNoticeByDefault(): void
    {
        $previous_locale = app()->getLocale();
        config()->set('affiliate.default_locale', 'th');
        app()->setLocale('en');

        try {
            $response = $this->get(route('affiliate.courses'));
        } finally {
            app()->setLocale($previous_locale);
        }

        $response->assertOk();
        $response->assertSee('แจ้งเตือนสำคัญเรื่องการชำระเงิน', false);
        $response->assertDontSee('Important payment notice', false);
    }
}
