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
        public readonly ?int $click_event_id = null,
        public readonly ?int $content_id = null,
        public readonly ?string $campaign_code = null,
        public readonly ?string $source_type = null,
        public readonly ?string $unique_window_key = null,
        public readonly ?string $destination_target = null,
    ) {
        if ($this->affiliate_profile_id < 1 || !$this->expires_at->greaterThan($this->captured_at)) {
            throw new InvalidArgumentException('Affiliate referral cookie values are invalid.');
        }
        $has_context = $this->click_event_id !== null
            || $this->content_id !== null
            || $this->campaign_code !== null
            || $this->source_type !== null
            || $this->unique_window_key !== null
            || $this->destination_target !== null;
        if (! $has_context) {
            return;
        }
        if ($this->click_event_id === null || $this->source_type === null || $this->unique_window_key === null || $this->destination_target === null) {
            throw new InvalidArgumentException('Affiliate referral cookie click context is incomplete.');
        }
        if ($this->click_event_id < 1 || ($this->content_id !== null && $this->content_id < 1)) {
            throw new InvalidArgumentException('Affiliate referral cookie click context identifiers are invalid.');
        }
        if (! in_array($this->source_type, ['direct', 'media'], true)) {
            throw new InvalidArgumentException('Affiliate referral cookie click context source type is invalid.');
        }
        if (! preg_match('/\A[0-9A-HJKMNP-TV-Z]{26}\z/', $this->unique_window_key)) {
            throw new InvalidArgumentException('Affiliate referral cookie click context window key is invalid.');
        }
        if ($this->destination_target === '') {
            throw new InvalidArgumentException('Affiliate referral cookie click context destination is invalid.');
        }
        if ($this->source_type === 'media' && ($this->content_id === null || $this->campaign_code === null || $this->campaign_code === '')) {
            throw new InvalidArgumentException('Affiliate referral cookie media context is incomplete.');
        }
        if ($this->source_type === 'direct' && ($this->content_id !== null || $this->campaign_code !== null)) {
            throw new InvalidArgumentException('Affiliate referral cookie direct context is inconsistent.');
        }
    }

    public static function FromArray(array $payload): self
    {
        if (!isset($payload['affiliate_profile_id'], $payload['captured_at'], $payload['expires_at'])) {
            throw new InvalidArgumentException('Affiliate referral cookie payload is incomplete.');
        }

        $click_event_id = $payload['click_event_id'] ?? null;
        $content_id = $payload['content_id'] ?? null;
        return new self(
            (int) $payload['affiliate_profile_id'],
            CarbonImmutable::parse($payload['captured_at'], 'UTC'),
            CarbonImmutable::parse($payload['expires_at'], 'UTC'),
            $click_event_id === null ? null : (int) $click_event_id,
            $content_id === null ? null : (int) $content_id,
            isset($payload['campaign_code']) ? (string) $payload['campaign_code'] : null,
            isset($payload['source_type']) ? (string) $payload['source_type'] : null,
            isset($payload['unique_window_key']) ? (string) $payload['unique_window_key'] : null,
            isset($payload['destination_target']) ? (string) $payload['destination_target'] : null,
        );
    }

    public function IsValidAt(DateTimeInterface $at): bool
    {
        $moment = CarbonImmutable::instance($at);
        return $this->captured_at->lessThanOrEqualTo($moment) && $moment->lessThan($this->expires_at);
    }

    public function ToArray(): array
    {
        $payload = [
            'affiliate_profile_id' => $this->affiliate_profile_id,
            'captured_at' => $this->captured_at->toIso8601String(),
            'expires_at' => $this->expires_at->toIso8601String(),
        ];
        foreach ([
            'click_event_id' => $this->click_event_id,
            'content_id' => $this->content_id,
            'campaign_code' => $this->campaign_code,
            'source_type' => $this->source_type,
            'unique_window_key' => $this->unique_window_key,
            'destination_target' => $this->destination_target,
        ] as $key => $value) {
            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }
}
