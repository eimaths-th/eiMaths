<?php

namespace Tests\Unit\Affiliate;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateTranslationStructureTest extends TestCase
{
    private const REQUIRED_NAMESPACES = [
        'navigation',
        'dashboard',
        'analytics',
        'media',
        'content_admin',
        'payout_accounts',
        'withdrawals',
        'statuses',
        'validation',
        'feedback',
        'landing',
        'errors',
    ];

    #[Test]
    public function AffiliateTranslationNamespacesArePresentInBothLocales(): void
    {
        $english = require base_path('lang/en/affiliate.php');
        $thai = require base_path('lang/th/affiliate.php');

        foreach (self::REQUIRED_NAMESPACES as $namespace) {
            $this->assertArrayHasKey($namespace, $english);
            $this->assertArrayHasKey($namespace, $thai);
        }
    }

    #[Test]
    public function EnglishAndThaiAffiliateTranslationLeavesAndPlaceholdersMatch(): void
    {
        $english = require base_path('lang/en/affiliate.php');
        $thai = require base_path('lang/th/affiliate.php');

        $english_leaves = $this->Flatten($english);
        $thai_leaves = $this->Flatten($thai);
        ksort($english_leaves);
        ksort($thai_leaves);

        $this->assertSame(array_keys($english_leaves), array_keys($thai_leaves));
        foreach ($english_leaves as $path => $english_value) {
            $this->assertSame(
                $this->Placeholders($english_value),
                $this->Placeholders($thai_leaves[$path]),
                "Placeholder mismatch at {$path}",
            );
        }
    }

    private function Flatten(array $tree, string $prefix = ''): array
    {
        $leaves = [];
        foreach ($tree as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $leaves += $this->Flatten($value, $path);
                continue;
            }
            $leaves[$path] = (string) $value;
        }

        return $leaves;
    }

    private function Placeholders(string $value): array
    {
        preg_match_all('/(?::|\{)([A-Za-z0-9_]+)\}?/', $value, $matches);
        $placeholders = array_values(array_unique($matches[1] ?? []));
        sort($placeholders);

        return $placeholders;
    }
}
