<?php

namespace App\Domain\Affiliate\Services;

use InvalidArgumentException;

final class AffiliateDestinationValidator
{
    public function Normalize(string $destination_target): string
    {
        if ($destination_target === '' || preg_match('/[\x00-\x1F\x7F]/', $destination_target) || str_contains($destination_target, '\\')) {
            throw new InvalidArgumentException('Affiliate destinations must be safe relative paths or HTTPS URLs.');
        }
        $destination_target = trim($destination_target);
        if ($destination_target === '') {
            throw new InvalidArgumentException('Affiliate destinations must be safe relative paths or HTTPS URLs.');
        }
        if (str_starts_with($destination_target, '//')) {
            throw new InvalidArgumentException('Affiliate destinations must not be protocol-relative URLs.');
        }

        if (str_starts_with($destination_target, '/')) {
            return $destination_target;
        }

        $parts = parse_url($destination_target);
        if (! is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            throw new InvalidArgumentException('Affiliate destinations must use HTTPS when absolute.');
        }
        if (! isset($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('Affiliate destinations must not contain credentials or a missing host.');
        }

        $host = strtolower((string) $parts['host']);

        $normalized = 'https://' . $host;
        if (isset($parts['port'])) {
            $normalized .= ':' . (int) $parts['port'];
        }
        $normalized .= (string) ($parts['path'] ?? '');
        if (isset($parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $normalized .= '#' . $parts['fragment'];
        }

        return $normalized;
    }
}
