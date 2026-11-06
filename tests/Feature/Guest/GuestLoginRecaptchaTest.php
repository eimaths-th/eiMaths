<?php

namespace Tests\Feature\Guest;

use App\Models\GuestQuizzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestLoginRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private const GUEST_EMAIL = 'guest@example.com';

    private const GUEST_PHONE = '0812345678';

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
        GuestQuizzer::create([
            'first_name' => 'Guest',
            'last_name' => 'Tester',
            'grade' => 'P1',
            'email' => self::GUEST_EMAIL,
            'phone' => self::GUEST_PHONE,
            'password' => Hash::make(self::GUEST_PHONE),
            'expires_at' => now()->addDays(10),
        ]);
    }

    private function FakeGoogleOutage(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('Connection timed out');
        });
    }

    private function SubmitLogin(string $password = self::GUEST_PHONE, ?string $token = 'token')
    {
        return $this->from('/students-login')->post(route('guest.quiz.login'), array_filter([
            'guest_email' => self::GUEST_EMAIL,
            'guest_password' => $password,
            'recaptcha_token' => $token,
        ]));
    }

    #[Test]
    public function BlocksALowScore(): void
    {
        Http::fake([self::VERIFY_URL => Http::response([
            'success' => true, 'score' => 0.1, 'action' => 'guest_login', 'hostname' => 'localhost',
        ])]);

        $this->SubmitLogin()
            ->assertSessionHasErrors(['guest_error' => __('students.login.recaptcha_rejected')])
            ->assertSessionMissing('_old_input.recaptcha_token')
            ->assertSessionMissing('_old_input.guest_password');

        $this->assertGuest('guest_quiz');
    }

    #[Test]
    public function BlocksAMissingToken(): void
    {
        Http::fake();

        $this->SubmitLogin(token: null)->assertSessionHasErrors('guest_error');

        $this->assertGuest('guest_quiz');
        Http::assertNothingSent();
    }

    #[Test]
    public function ContinuesWithCorrectCredentialsDuringAnOutage(): void
    {
        $this->FakeGoogleOutage();

        $this->SubmitLogin()->assertRedirect(route('guest.quiz.home'));

        $this->assertAuthenticated('guest_quiz');
    }

    #[Test]
    public function StillRefusesWrongCredentialsDuringAnOutage(): void
    {
        $this->FakeGoogleOutage();

        $this->SubmitLogin('0000000000')->assertSessionHasErrors('guest_error');

        $this->assertGuest('guest_quiz');
    }

    #[Test]
    public function NeverFlashesThePasswordOrTokenAfterFailedLogins(): void
    {
        $this->FakeGoogleOutage();

        $this->SubmitLogin('0000000000')
            ->assertSessionHasInput('guest_email', self::GUEST_EMAIL)
            ->assertSessionMissing('_old_input.guest_password')
            ->assertSessionMissing('_old_input.recaptcha_token');

        $this->from('/students-login')->post(route('guest.quiz.login'), [
            'guest_password' => 'secret-phone',
            'recaptcha_token' => 'token',
        ])
            ->assertSessionMissing('_old_input.guest_password')
            ->assertSessionMissing('_old_input.recaptcha_token');
    }

    #[Test]
    public function KeepsTheGuestPanelForAnOversizedToken(): void
    {
        Http::fake();

        $this->SubmitLogin(token: str_repeat('a', 4001))->assertSessionHasErrors('guest_error');
    }

    #[Test]
    public function RendersTheLoginPageAfterAnArrayEmailWasSubmitted(): void
    {
        Http::fake();

        $this->from('/students-login')->post(route('guest.quiz.login'), ['guest_email' => ['guest@example.com']]);

        $this->get('/students-login')->assertOk();
    }

    #[Test]
    public function SkipsTheCheckWhenDisabled(): void
    {
        config(['services.recaptcha.enabled' => false]);
        Http::fake();

        $this->SubmitLogin(token: null)->assertRedirect(route('guest.quiz.home'));

        Http::assertNothingSent();
    }

    #[Test]
    public function RendersProtectionOnTheLoginForm(): void
    {
        $this->get('/students-login')
            ->assertOk()
            ->assertSee('data-recaptcha-action="guest_login"', false);
    }
}
