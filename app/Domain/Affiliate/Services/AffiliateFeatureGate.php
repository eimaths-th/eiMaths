<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;

class AffiliateFeatureGate
{
    public function IsEnabled(string $feature_name): bool
    {
        $features = config('affiliate.features', []);
        return array_key_exists($feature_name, $features) && (bool) $features[$feature_name];
    }

    public function AssertEnabled(string $feature_name): void
    {
        $features = config('affiliate.features', []);
        if (!array_key_exists($feature_name, $features)) {
            throw AffiliateDomainException::ConfigurationInvalid('Unknown Affiliate feature.');
        }
        if (!(bool) $features[$feature_name]) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate feature is disabled.');
        }
    }
}
