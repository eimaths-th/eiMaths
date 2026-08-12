<?php

return [
    'credit_note' => [
        'enabled' => env('CREDIT_NOTE_FEATURE_ENABLED', false),
        'financial_actions_enabled' => env('CREDIT_NOTE_FINANCIAL_ACTIONS_ENABLED', false),
        'issuer' => [
            'legal_name' => env('CREDIT_NOTE_ISSUER_LEGAL_NAME', ''),
            'legal_address' => env('CREDIT_NOTE_ISSUER_LEGAL_ADDRESS', ''),
            'tax_id' => env('CREDIT_NOTE_ISSUER_TAX_ID', ''),
            'branch_type' => env('CREDIT_NOTE_ISSUER_BRANCH_TYPE', 'head_office'),
            'branch_code' => env('CREDIT_NOTE_ISSUER_BRANCH_CODE', '00000'),
        ],
        'limits' => [
            'list_per_page' => 50,
            'reconciliation_batch' => 100,
            'source_audit_batch' => 500,
            'transaction_attempts' => 3,
        ],
    ],
];
