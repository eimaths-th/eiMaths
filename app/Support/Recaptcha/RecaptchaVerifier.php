<?php

namespace App\Support\Recaptcha;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaVerifier
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private const TIMEOUT_SECONDS = 3;

    private const MAX_TOKEN_LENGTH = 4000;

    private const SECRET_ERROR_CODES = ['missing-input-secret', 'invalid-input-secret'];

    public function IsEnabled(): bool
    {
        if (! config('services.recaptcha.enabled')) {
            return false;
        }

        if ($this->SiteKey() === '' || $this->SecretKey() === '') {
            Log::error('RECAPTCHA_KEYS_MISSING');

            return false;
        }

        return true;
    }

    public function SiteKey(): string
    {
        return (string) config('services.recaptcha.site_key');
    }

    public function Verify(mixed $token, string $action, ?string $ip = null): RecaptchaResult
    {
        if (! is_string($token) || $token === '' || strlen($token) > self::MAX_TOKEN_LENGTH) {
            return $this->LogResult($action, RecaptchaResult::REJECTED);
        }

        try {
            $response = Http::asForm()
                ->timeout(self::TIMEOUT_SECONDS)
                ->post(self::VERIFY_URL, array_filter([
                    'secret' => $this->SecretKey(),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (HttpClientException|TransferException) {
            return $this->LogResult($action, RecaptchaResult::UNAVAILABLE);
        }

        if (! $response->successful()) {
            return $this->LogResult($action, RecaptchaResult::UNAVAILABLE);
        }

        $body = $response->json();
        $score = is_numeric($body['score'] ?? null) ? (float) $body['score'] : null;

        return $this->LogResult($action, $this->EvaluateResponse(is_array($body) ? $body : [], $action, $score), $score);
    }

    private function EvaluateResponse(array $body, string $action, ?float $score): RecaptchaResult
    {
        if (($body['success'] ?? false) !== true) {
            return array_intersect($body['error-codes'] ?? [], self::SECRET_ERROR_CODES) === []
                ? RecaptchaResult::REJECTED
                : RecaptchaResult::UNAVAILABLE;
        }

        $is_expected_request = in_array($body['hostname'] ?? '', $this->AllowedHostnames(), true)
            && hash_equals($action, (string) ($body['action'] ?? ''));
        $is_score_sufficient = $score !== null && $score >= (float) config('services.recaptcha.min_score');

        return $is_expected_request && $is_score_sufficient ? RecaptchaResult::PASSED : RecaptchaResult::REJECTED;
    }

    private function LogResult(string $action, RecaptchaResult $result, ?float $score = null): RecaptchaResult
    {
        $log_level = $result === RecaptchaResult::UNAVAILABLE ? 'warning' : 'info';
        Log::{$log_level}('RECAPTCHA_CHECK', [
            'recaptcha_action' => $action,
            'recaptcha_result' => $result->value,
            'recaptcha_score' => $score,
        ]);

        return $result;
    }

    private function AllowedHostnames(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('services.recaptcha.allowed_hostnames'))
        )));
    }

    private function SecretKey(): string
    {
        return (string) config('services.recaptcha.secret_key');
    }
}
