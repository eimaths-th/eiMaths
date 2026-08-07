<?php

namespace App\Domain\Affiliate\ValueObjects;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class AffiliateReferralCookie
{
    public function __construct(
        public readonly int $affiliate_profile_id,
        public readonly CarbonImmutable $captured_at,
        public readonly CarbonImmutable $expires_at,
    ) {
        if ($this->affiliate_profile_id < 1 || !$this->expires_at->greaterThan($this->captured_at)) {
            throw new InvalidArgumentException('Affiliate referral cookie values are invalid.');
        }
    }

    public static function FromArray(array $payload): self
    {
        if (!isset($payload['affiliate_profile_id'], $payload['captured_at'], $payload['expires_at'])) {
            throw new InvalidArgumentException('Affiliate referral cookie payload is incomplete.');
        }

        return new self(
            (int) $payload['affiliate_profile_id'],
            CarbonImmutable::parse($payload['captured_at'], 'UTC'),
            CarbonImmutable::parse($payload['expires_at'], 'UTC'),
        );
    }

    public function IsValidAt(DateTimeInterface $at): bool
    {
        $moment = CarbonImmutable::instance($at);
        return $this->captured_at->lessThanOrEqualTo($moment) && $moment->lessThan($this->expires_at);
    }

    public function ToArray(): array
    {
        return [
            'affiliate_profile_id' => $this->affiliate_profile_id,
            'captured_at' => $this->captured_at->toIso8601String(),
            'expires_at' => $this->expires_at->toIso8601String(),
        ];
    }
}
