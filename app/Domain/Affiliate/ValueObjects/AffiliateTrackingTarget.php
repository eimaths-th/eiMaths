<?php

namespace App\Domain\Affiliate\ValueObjects;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\AffiliateContent;
use InvalidArgumentException;

final readonly class AffiliateTrackingTarget
{
    public function __construct(
        public AffiliateProfile $affiliate_profile,
        public ?AffiliateContent $affiliate_content,
        public ?string $campaign_code,
        public string $source_type,
        public string $destination_target,
    ) {
        if (! in_array($this->source_type, ['direct', 'media'], true)) {
            throw new InvalidArgumentException('Affiliate tracking source type is invalid.');
        }
        if ($this->source_type === 'media' && ($this->affiliate_content === null || $this->campaign_code === null)) {
            throw new InvalidArgumentException('Affiliate media tracking context is incomplete.');
        }
    }
}
