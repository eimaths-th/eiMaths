<?php

return [
    // The legacy Demo Student flow remains disabled until it has dedicated
    // permissions, an expiry policy, and a supported conversion workflow.
    'enabled' => env('DEMO_STUDENTS_ENABLED', false),
];
