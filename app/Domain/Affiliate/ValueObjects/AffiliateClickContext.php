<?php

namespace App\Domain\Affiliate\ValueObjects;

use InvalidArgumentException;

final readonly class AffiliateClickContext
{
    public function __construct(
        public int $click_event_id,
        public ?int $content_id,
        public ?string $campaign_code,
        public string $source_type,
        public string $unique_window_key,
        public string $destination_target,
        public bool $is_unique = false,
    ) {
        if ($this->click_event_id < 1 || ($this->content_id !== null && $this->content_id < 1)) {
            throw new InvalidArgumentException('Affiliate click context identifiers are invalid.');
        }
        if (! in_array($this->source_type, ['direct', 'media'], true)) {
            throw new InvalidArgumentException('Affiliate click context source type is invalid.');
        }
        if (! preg_match('/\A[0-9A-HJKMNP-TV-Z]{26}\z/', $this->unique_window_key)) {
            throw new InvalidArgumentException('Affiliate click context window key is invalid.');
        }
        if ($this->source_type === 'media' && ($this->content_id === null || $this->campaign_code === null || $this->campaign_code === '')) {
            throw new InvalidArgumentException('Affiliate media click context is incomplete.');
        }
    }
}
