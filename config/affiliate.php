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
        'click_tracking' => env('AFFILIATE_FEATURE_CLICK_TRACKING', false),
        'analytics' => env('AFFILIATE_FEATURE_ANALYTICS', false),
        'media_library' => env('AFFILIATE_FEATURE_MEDIA_LIBRARY', false),
        'employee_enrollment' => env('AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT', false),
    ],

    'employee_roles' => array_values(array_filter(array_map(
        static fn (string $role_name): string => trim($role_name),
        explode(',', (string) env(
            'AFFILIATE_EMPLOYEE_ROLES',
            'Admin,SystemAdmin,BranchAdmin,Teacher,blog',
        )),
    ))),

    'default_locale' => env('AFFILIATE_DEFAULT_LOCALE', 'th'),

    'supported_locales' => ['th', 'en'],

    'legal' => [
        'terms_version' => '2026-09-06',
        'privacy_version' => '2026-09-06',
        'effective_date' => '2026-09-06',
        'support_phone' => '06-1620-8666',
    ],

    'legal' => [
        'terms_version' => '2026-09-06',
        'privacy_version' => '2026-09-06',
        'effective_date' => '2026-09-06',
        'support_phone' => '06-1620-8666',
    ],

    'visitor_hash_key' => env('AFFILIATE_VISITOR_HASH_KEY'),

    'allowed_destination_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => strtolower(trim($host)),
        explode(',', (string) env(
            'AFFILIATE_ALLOWED_DESTINATION_HOSTS',
            parse_url((string) env('APP_URL'), PHP_URL_HOST) ?: '',
        )),
    ))),

    'maintenance_mode' => env('AFFILIATE_MAINTENANCE_MODE', false),

    'abuse' => [
        'registration_attempts_per_minute' => env('AFFILIATE_REGISTRATION_ATTEMPTS_PER_MINUTE', 5),
        'click_attempts_per_minute' => env('AFFILIATE_CLICK_ATTEMPTS_PER_MINUTE', 120),
        'employee_enrollment_attempts_per_minute' => env('AFFILIATE_EMPLOYEE_ENROLLMENT_ATTEMPTS_PER_MINUTE', 6),
        'known_bot_tokens' => [
            'bot',
            'crawler',
            'spider',
            'slurp',
            'bingpreview',
            'facebookexternalhit',
            'whatsapp',
            'telegrambot',
            'discordbot',
        ],
    ],

    'batch_sizes' => [
        'commission_release' => 100,
        'reconciliation' => 100,
        'legacy_migration' => 100,
        'click_retention' => 1000,
        'click_reconciliation' => 1000,
    ],
];
