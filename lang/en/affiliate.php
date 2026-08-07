<?php

return [
    'errors' => [
        'domain' => 'The Affiliate operation could not be completed.',
        'financial_source_locked' => 'This financial source is locked because commission evidence already exists.',
        'state_conflict' => 'The Affiliate resource is not in a valid state for this operation.',
        'ownership_denied' => 'You are not authorized to access this Affiliate resource.',
        'configuration_invalid' => 'Affiliate configuration is unavailable or invalid.',
        'insufficient_balance' => 'The available balance is insufficient.',
        'negative_balance_withdrawal' => 'Withdrawal is unavailable while the available balance is not positive.',
        'actor_separation' => 'The approving and paying users must be different.',
        'idempotency_conflict' => 'The idempotency key was already used for another operation.',
    ],
];
