<?php

namespace App\Domain\Affiliate\ValueObjects;

final readonly class StoredAffiliateMedia
{
    public function __construct(
        public string $type,
        public string $relative_path,
        public string $absolute_path,
        public string $mime_type,
        public string $extension,
    ) {
    }
}
