<?php

namespace Tests\Feature\Guest;

use App\Models\GuestQuizzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GuestRegistrationConsentTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private const LOW_SCORE_RESPONSE = ['success' => true, 'score' => 0.1, 'action' => 'guest_register', 'hostname' => 'localhost'];

    private const PASSING_RESPONSE = ['success' => true, 'score' => 0.9, 'action' => 'guest_register', 'hostname' => 'localhost'];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.recaptcha' => [
            'enabled' => true,
            'site_key' => 'public-site-key',
            'secret_key' => 'private-secret-key',
            'min_score' => 0.5,
            'allowed_hostnames' => 'localhost',
        ]]);
    }

    private function RegistrationInput(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Guest',
            'last_name' => 'Tester',
            'grade' => 'P1',
            'email' => 'guest@example.com',
            'phone' => '0812345678',
            'pdpa' => '1',
            'privacy_notice_version' => 'p1-3-v1',
            'recaptcha_token' => 'token',
        ], $overrides);
    }

    private function SubmitRegistration(array $overrides = [])
    {
        return $this->from('/free-quiz/register')->post(route('guest.quiz.register.store'), $this->RegistrationInput($overrides));
    }

    #[Test]
    public function StoresEvidenceWithMarketingConsent(): void
    {
        Http::fake([self::VERIFY_URL => Http::response(self::PASSING_RESPONSE)]);

        $this->SubmitRegistration(['marketing_consent' => '1'])->assertRedirect(route('guest.quiz.home'));

        $guest = GuestQuizzer::query()->sole();
        $this->assertSame('p1-3-v1', $guest->privacy_notice_version);
        $this->assertNotNull($guest->privacy_acknowledged_at);
        $this->assertTrue($guest->marketing_consent);
        $this->assertSame('marketing-v1', $guest->marketing_consent_version);
        $this->assertNotNull($guest->marketing_consent_recorded_at);
    }

    #[Test]
    public function StoresDeclinedMarketingAsFalse(): void
    {
        Http::fake([self::VERIFY_URL => Http::response(self::PASSING_RESPONSE)]);

        $this->SubmitRegistration()->assertRedirect(route('guest.quiz.home'));

        $guest = GuestQuizzer::query()->sole();
        $this->assertFalse($guest->marketing_consent);
        $this->assertNotNull($guest->marketing_consent_recorded_at);
    }

    #[Test]
    public function RequiresThePrivacyAcknowledgement(): void
    {
        Http::fake();

        $this->SubmitRegistration(['pdpa' => null])->assertSessionHasErrors('pdpa');

        $this->assertSame(0, GuestQuizzer::query()->count());
    }

    #[Test]
    public function RefusesAStaleNoticeVersion(): void
    {
        Http::fake();

        $this->SubmitRegistration(['privacy_notice_version' => 'old-version'])->assertSessionHasErrors('privacy_notice_version');

        $this->assertSame(0, GuestQuizzer::query()->count());
    }

    public static function FailedRecaptchaCases(): array
    {
        return [
            'low score' => ['low_score'],
            'missing token' => ['missing_token'],
            'google outage' => ['outage'],
        ];
    }

    #[Test]
    #[DataProvider('FailedRecaptchaCases')]
    public function RejectsEveryFailedCheckAndKeepsInput(string $failure_case): void
    {
        $overrides = ['marketing_consent' => '1'];
        if ($failure_case === 'low_score') {
            Http::fake([self::VERIFY_URL => Http::response(self::LOW_SCORE_RESPONSE)]);
        } elseif ($failure_case === 'outage') {
            Http::fake(function (): void {
                throw new ConnectionException('Connection timed out');
            });
        } else {
            Http::fake();
            $overrides['recaptcha_token'] = null;
        }

        $this->SubmitRegistration($overrides)
            ->assertRedirect('/free-quiz/register')
            ->assertSessionHasErrors('registration')
            ->assertSessionHasInput('first_name', 'Guest')
            ->assertSessionHasInput('marketing_consent', '1')
            ->assertSessionMissing('_old_input.recaptcha_token');

        $this->assertSame(0, GuestQuizzer::query()->count());
        $this->assertGuest('guest_quiz');
    }

    #[Test]
    public function WorksWithoutRecaptchaWhenDisabledButStillRequiresThePrivacyAcknowledgement(): void
    {
        config(['services.recaptcha.enabled' => false]);
        Http::fake();

        $this->SubmitRegistration(['pdpa' => null, 'recaptcha_token' => null])->assertSessionHasErrors('pdpa');
        $this->SubmitRegistration(['recaptcha_token' => null])->assertRedirect(route('guest.quiz.home'));

        $this->assertSame(1, GuestQuizzer::query()->count());
        Http::assertNothingSent();
    }

    #[Test]
    public function NeverFlashesTheTokenWhenValidationFails(): void
    {
        Http::fake();

        $this->SubmitRegistration(['pdpa' => null])->assertSessionMissing('_old_input.recaptcha_token');
    }

    #[Test]
    public function TreatsAnOversizedTokenAsARejection(): void
    {
        Http::fake();

        $this->SubmitRegistration(['recaptcha_token' => str_repeat('a', 4001)])->assertSessionHasErrors('registration');

        $this->assertSame(0, GuestQuizzer::query()->count());
    }

    public static function RegistrationPages(): array
    {
        return ['login page panel' => ['/students-login'], 'dedicated page' => ['/free-quiz/register']];
    }

    #[Test]
    #[DataProvider('RegistrationPages')]
    public function RendersUncheckedConsentAndProtection(string $page_path): void
    {
        $page_html = $this->get($page_path)->assertOk()->getContent();

        $this->assertStringContainsString('data-recaptcha-action="guest_register"', $page_html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="pdpa"[^>]*required[^>]*>/', $page_html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*name="pdpa"[^>]*checked/', $page_html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*name="marketing_consent"[^>]*checked/', $page_html);
        $this->assertStringContainsString('name="privacy_notice_version" value="p1-3-v1"', $page_html);
    }

    #[Test]
    public function RestoresTheApplicantsChoiceAfterAFailedSubmission(): void
    {
        Http::fake([self::VERIFY_URL => Http::response(self::LOW_SCORE_RESPONSE)]);

        $this->SubmitRegistration(['marketing_consent' => '1'])->assertRedirect('/free-quiz/register');

        $page_html = $this->get('/free-quiz/register')->getContent();
        $this->assertMatchesRegularExpression('/<input[^>]*name="marketing_consent"[^>]*checked/', $page_html);
    }

    #[Test]
    public function LeavesExistingRowsWithoutConsent(): void
    {
        DB::table('guest_quizzers')->insert([
            'first_name' => 'Old',
            'last_name' => 'Guest',
            'grade' => 'P1',
            'email' => 'old@example.com',
            'phone' => '0800000000',
            'password' => Hash::make('0800000000'),
            'expires_at' => now()->addDays(1),
        ]);

        $this->assertNull(GuestQuizzer::query()->sole()->marketing_consent);
    }

    #[Test]
    public function RefusesToDropConsentEvidence(): void
    {
        $migration = require database_path('migrations/2026_11_06_000002_add_privacy_consent_to_guest_quizzers_table.php');

        $this->expectException(RuntimeException::class);

        $migration->down();
    }
}
