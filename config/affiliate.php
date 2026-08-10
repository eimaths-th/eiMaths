<?php

return [
    'cookie_name' => 'eimaths_affiliate_referral',

    'features' => [
        'referral_capture' => env('AFFILIATE_FEATURE_REFERRAL_CAPTURE', false),
        'commission_creation' => env('AFFILIATE_FEATURE_COMMISSION_CREATION', false),
        'commission_release' => env('AFFILIATE_FEATURE_COMMISSION_RELEASE', false),
        'confirmed_refunds' => env('AFFILIATE_FEATURE_CONFIRMED_REFUNDS', false),
        'wallet_withdrawals' => env('AFFILIATE_FEATURE_WALLET_WITHDRAWALS', false),
        'administration' => env('AFFILIATE_FEATURE_ADMINISTRATION', false),
    ],

    'maintenance_mode' => env('AFFILIATE_MAINTENANCE_MODE', false),

    'abuse' => [
        'registration_attempts_per_minute' => env('AFFILIATE_REGISTRATION_ATTEMPTS_PER_MINUTE', 5),
    ],

    'batch_sizes' => [
        'commission_release' => 100,
        'reconciliation' => 100,
        'legacy_migration' => 100,
    ],
];
