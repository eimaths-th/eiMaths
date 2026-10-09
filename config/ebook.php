<?php

return [
    // Available in every environment; staff permissions and student sessions control access.
    'demo' => false,
    'root' => storage_path('app/private/ebooks'),
    'pdfinfo' => env('EBOOK_PDFINFO', 'pdfinfo'),
    'pdftoppm' => env('EBOOK_PDFTOPPM', 'pdftoppm'),
    'max_pages' => 120,
    'session_idle_minutes' => 30,
    'session_max_hours' => 24,
];
