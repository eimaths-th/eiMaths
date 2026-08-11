<?php

namespace App\Domain\Affiliate\Services;

use Illuminate\Http\Request;
use InvalidArgumentException;

final class AffiliateVisitorHasher
{
    private const MINIMUM_KEY_LENGTH = 32;

    public function HashRequest(Request $request): string
    {
        $key = (string) config('affiliate.visitor_hash_key', '');
        if (strlen($key) < self::MINIMUM_KEY_LENGTH) {
            throw new InvalidArgumentException('Affiliate visitor hashing requires a key of at least 32 characters.');
        }

        $normalized_ip = filter_var($request->ip(), FILTER_VALIDATE_IP);
        $normalized_ip = $normalized_ip === false ? '' : (string) $normalized_ip;
        $normalized_user_agent = $this->NormalizeUserAgent($request->userAgent());

        return hash_hmac('sha256', $normalized_ip . "\n" . $normalized_user_agent, $key);
    }

    private function NormalizeUserAgent(?string $user_agent): string
    {
        $truncated = function_exists('mb_substr')
            ? mb_substr((string) $user_agent, 0, 512, 'UTF-8')
            : substr((string) $user_agent, 0, 512);

        return (string) preg_replace('/\s+/u', ' ', strtolower(trim($truncated)));
    }
}
