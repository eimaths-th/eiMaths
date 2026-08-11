<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Models\AffiliateProfile;
use Illuminate\Http\Request;

final class AffiliateTrafficClassifier
{
    public const REASON_KNOWN_BOT = 'known_bot';
    public const REASON_AUTHENTICATED_OWNER = 'authenticated_owner';

    public function RejectionReason(Request $request, AffiliateProfile $profile): ?string
    {
        if (auth()->check() && (int) auth()->id() === (int) $profile->user_id) {
            return self::REASON_AUTHENTICATED_OWNER;
        }

        $user_agent = $this->NormalizeUserAgent($request->userAgent());
        foreach ((array) config('affiliate.abuse.known_bot_tokens', []) as $token) {
            $normalized_token = strtolower(trim((string) $token));
            if ($normalized_token !== '' && str_contains($user_agent, $normalized_token)) {
                return self::REASON_KNOWN_BOT;
            }
        }

        return null;
    }

    private function NormalizeUserAgent(?string $user_agent): string
    {
        $truncated = function_exists('mb_substr')
            ? mb_substr((string) $user_agent, 0, 512, 'UTF-8')
            : substr((string) $user_agent, 0, 512);

        return (string) preg_replace('/\s+/u', ' ', strtolower(trim($truncated)));
    }
}
