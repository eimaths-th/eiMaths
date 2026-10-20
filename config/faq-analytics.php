<?php
return [
    'integration_token' => env('FAQ_READ_INTEGRATION_TOKEN', ''),
    'integration_requests_per_minute' => (int) env('FAQ_READ_INTEGRATION_REQUESTS_PER_MINUTE', 600),
    'invalid_requests_per_minute' => (int) env('FAQ_READ_INVALID_REQUESTS_PER_MINUTE', 30),
];
