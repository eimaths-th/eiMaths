<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentLoginRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

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
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
        Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }

    private function FakeGoogleScore(float $score): void
    {
        Http::fake([self::VERIFY_URL => Http::response([
            'success' => true,
            'score' => $score,
            'action' => 'student_login',
            'hostname' => 'localhost',
        ])]);
    }

    private function FakeGoogleOutage(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('Connection timed out');
        });
    }

    private function SubmitLogin(string $password = 'correct-password', ?string $token = 'token')
    {
        return $this->from('/students-login')->post('/students-login-check', array_filter([
            'code' => 'BR0001',
            'password' => $password,
            'recaptcha_token' => $token,
        ]));
    }

    #[Test]
    public function BlocksALowScoreWithLoginErrorMessage(): void
    {
        $this->FakeGoogleScore(0.1);

        $this->SubmitLogin()
            ->assertRedirect('/students-login')
            ->assertSessionHasErrors(['error' => __('students.login.recaptcha_rejected')]);
        $this->assertGuest('student');
    }

    #[Test]
    public function BlocksAMissingTokenWithoutCallingGoogle(): void
    {
        Http::fake();

        $this->SubmitLogin(token: null)->assertSessionHasErrors('error');

        $this->assertGuest('student');
        Http::assertNothingSent();
    }

    #[Test]
    public function ContinuesWithCorrectPasswordDuringAnOutage(): void
    {
        $this->FakeGoogleOutage();

        $this->SubmitLogin()->assertRedirect(route('home3'));

        $this->assertAuthenticated('student');
    }

    #[Test]
    public function StillRefusesAWrongPasswordDuringAnOutage(): void
    {
        $this->FakeGoogleOutage();

        $this->SubmitLogin('wrong-password')->assertSessionHasErrors('error');

        $this->assertGuest('student');
    }

    #[Test]
    public function LogsInAfterAPassingCheck(): void
    {
        $this->FakeGoogleScore(0.9);

        $this->SubmitLogin()->assertRedirect(route('home3'));

        $this->assertAuthenticated('student');
    }

    #[Test]
    public function KeepsTheCodeAndTheLoginPanelAfterARejection(): void
    {
        $this->FakeGoogleScore(0.1);

        $this->SubmitLogin()->assertSessionHasInput('code', 'BR0001');

        $this->get('/students-login')
            ->assertSee('value="BR0001"', false)
            ->assertSee('openStudentForm();', false);
    }

    #[Test]
    public function KeepsTheStudentPanelForAnOversizedToken(): void
    {
        Http::fake();

        $this->SubmitLogin(token: str_repeat('a', 4001))->assertSessionHasErrors('error');

        Http::assertNothingSent();
    }

    #[Test]
    public function NeverFlashesTheTokenOnAValidationFailure(): void
    {
        Http::fake();

        $this->from('/students-login')->post('/students-login-check', ['recaptcha_token' => 'token'])
            ->assertSessionMissing('_old_input.recaptcha_token');
    }

    #[Test]
    public function RendersTheLoginPageAfterAnArrayCodeWasSubmitted(): void
    {
        Http::fake();

        $this->from('/students-login')->post('/students-login-check', ['code' => ['BR0001']]);

        $this->get('/students-login')->assertOk();
    }

    #[Test]
    public function SkipsTheCheckWhenDisabled(): void
    {
        config(['services.recaptcha.enabled' => false]);
        Http::fake();

        $this->SubmitLogin(token: null)->assertRedirect(route('home3'));

        Http::assertNothingSent();
    }

    #[Test]
    public function RendersProtectionAndPrivacyLinkWithoutASecret(): void
    {
        $this->get('/students-login')
            ->assertOk()
            ->assertSee('data-recaptcha-action="student_login"', false)
            ->assertSee('name="recaptcha_token"', false)
            ->assertSee('recaptcha/api.js?render=public-site-key', false)
            ->assertSee('href="'.config('privacy.notice_url').'"', false)
            ->assertDontSee('private-secret-key');
    }

    #[Test]
    public function RendersNoGoogleScriptWhenDisabled(): void
    {
        config(['services.recaptcha.enabled' => false]);

        $this->get('/students-login')
            ->assertOk()
            ->assertDontSee('recaptcha/api.js', false)
            ->assertSee('href="'.config('privacy.notice_url').'"', false);
    }
}
