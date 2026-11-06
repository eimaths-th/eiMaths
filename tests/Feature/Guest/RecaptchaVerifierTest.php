<?php

namespace Tests\Feature\Guest;

use App\Support\Recaptcha\RecaptchaResult;
use App\Support\Recaptcha\RecaptchaVerifier;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecaptchaVerifierTest extends TestCase
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.recaptcha' => [
            'enabled' => true,
            'site_key' => 'site',
            'secret_key' => 'secret',
            'min_score' => 0.5,
            'allowed_hostnames' => 'localhost, app.eimaths-th.com',
        ]]);
    }

    private function PassingResponse(array $overrides = []): array
    {
        return array_merge([
            'success' => true,
            'score' => 0.9,
            'action' => 'student_login',
            'hostname' => 'app.eimaths-th.com',
        ], $overrides);
    }

    private function Verify(?string $token = 'token'): RecaptchaResult
    {
        return app(RecaptchaVerifier::class)->Verify($token, 'student_login', '203.0.113.5');
    }

    public static function AbsentTokens(): array
    {
        return ['null token' => [null], 'empty token' => ['']];
    }

    #[Test]
    #[DataProvider('AbsentTokens')]
    public function RejectsAnAbsentTokenWithoutCallingGoogle(?string $token): void
    {
        Http::fake();

        $this->assertSame(RecaptchaResult::REJECTED, $this->Verify($token));
        Http::assertNothingSent();
    }

    #[Test]
    public function PassesAValidResponse(): void
    {
        Http::fake([self::VERIFY_URL => Http::response($this->PassingResponse())]);

        $this->assertSame(RecaptchaResult::PASSED, $this->Verify());
        Http::assertSent(fn ($request) => $request['secret'] === 'secret' && $request['response'] === 'token');
    }

    #[Test]
    public function PassesAScoreExactlyAtTheMinimum(): void
    {
        Http::fake([self::VERIFY_URL => Http::response($this->PassingResponse(['score' => 0.5]))]);

        $this->assertSame(RecaptchaResult::PASSED, $this->Verify());
    }

    public static function RejectedResponses(): array
    {
        return [
            'low score' => [['score' => 0.49]],
            'wrong action' => [['action' => 'guest_login']],
            'wrong hostname' => [['hostname' => 'evil.test']],
            'expired token' => [['success' => false, 'error-codes' => ['timeout-or-duplicate']]],
            'failure without codes' => [['success' => false]],
        ];
    }

    #[Test]
    #[DataProvider('RejectedResponses')]
    public function RejectsResponsesThatFailACheck(array $overrides): void
    {
        Http::fake([self::VERIFY_URL => Http::response($this->PassingResponse($overrides))]);

        $this->assertSame(RecaptchaResult::REJECTED, $this->Verify());
    }

    public static function SecretErrorCodes(): array
    {
        return ['invalid secret' => ['invalid-input-secret'], 'missing secret' => ['missing-input-secret']];
    }

    #[Test]
    #[DataProvider('SecretErrorCodes')]
    public function TreatsSecretErrorsAsUnavailable(string $error_code): void
    {
        Http::fake([self::VERIFY_URL => Http::response(['success' => false, 'error-codes' => [$error_code]])]);

        $this->assertSame(RecaptchaResult::UNAVAILABLE, $this->Verify());
    }

    #[Test]
    public function TreatsAServerErrorAsUnavailable(): void
    {
        Http::fake([self::VERIFY_URL => Http::response('down', 500)]);

        $this->assertSame(RecaptchaResult::UNAVAILABLE, $this->Verify());
    }

    #[Test]
    public function TreatsAConnectionFailureAsUnavailable(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('Connection timed out');
        });

        $this->assertSame(RecaptchaResult::UNAVAILABLE, $this->Verify());
    }

    #[Test]
    public function TreatsOtherTransferFailuresAsUnavailable(): void
    {
        Http::fake(function (): void {
            throw new RequestException('cURL error 56: Connection reset', new GuzzleRequest('POST', self::VERIFY_URL));
        });

        $this->assertSame(RecaptchaResult::UNAVAILABLE, $this->Verify());
    }

    public static function MalformedTokens(): array
    {
        return ['oversized token' => [str_repeat('a', 4001)], 'array token' => [['token']]];
    }

    #[Test]
    #[DataProvider('MalformedTokens')]
    public function RejectsMalformedTokensWithoutCallingGoogle(mixed $token): void
    {
        Http::fake();

        $result = app(RecaptchaVerifier::class)->Verify($token, 'student_login');

        $this->assertSame(RecaptchaResult::REJECTED, $result);
        Http::assertNothingSent();
    }

    #[Test]
    public function IsEnabledOnlyWithTheFlagAndBothKeys(): void
    {
        $this->assertTrue(app(RecaptchaVerifier::class)->IsEnabled());
        $this->assertSame('site', app(RecaptchaVerifier::class)->SiteKey());

        config(['services.recaptcha.enabled' => false]);
        $this->assertFalse(app(RecaptchaVerifier::class)->IsEnabled());
    }

    public static function MissingKeyNames(): array
    {
        return ['site key' => ['site_key'], 'secret key' => ['secret_key']];
    }

    #[Test]
    #[DataProvider('MissingKeyNames')]
    public function DisablesAndLogsAnErrorWhenAKeyIsMissing(string $key_name): void
    {
        config(["services.recaptcha.{$key_name}" => '']);
        Log::spy();

        $this->assertFalse(app(RecaptchaVerifier::class)->IsEnabled());
        Log::shouldHaveReceived('error')->once();
    }

    #[Test]
    public function LogsPassedChecksAtInfoWithoutSensitiveContext(): void
    {
        Http::fake([self::VERIFY_URL => Http::response($this->PassingResponse())]);
        Log::spy();

        $this->Verify();

        Log::shouldHaveReceived('info')->once()->withArgs(function (string $event, array $context): bool {
            return $event === 'RECAPTCHA_CHECK'
                && array_keys($context) === ['recaptcha_action', 'recaptcha_result', 'recaptcha_score']
                && $context['recaptcha_action'] === 'student_login'
                && $context['recaptcha_result'] === 'PASSED'
                && $context['recaptcha_score'] === 0.9;
        });
    }

    #[Test]
    public function LogsUnavailableChecksAtWarning(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('Connection timed out');
        });
        Log::spy();

        $this->Verify();

        Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $event, array $context): bool => $event === 'RECAPTCHA_CHECK'
            && $context['recaptcha_result'] === 'UNAVAILABLE'
            && $context['recaptcha_score'] === null);
    }
}
