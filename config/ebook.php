<?php

return [
    // Local development can use the module immediately after its migration.
    // Other environments still require an explicit EBOOK_ENABLED=true.
    'enabled' => (bool) env('EBOOK_ENABLED', env('APP_ENV', 'production') === 'local'),
    'demo' => false,
    'root' => storage_path('app/private/ebooks'),
    'pdfinfo' => env('EBOOK_PDFINFO', 'pdfinfo'),
    'pdftoppm' => env('EBOOK_PDFTOPPM', 'pdftoppm'),
    'max_pages' => 120,
    'session_idle_minutes' => 30,
    'session_max_hours' => 24,
];
