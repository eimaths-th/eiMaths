<?php

return [
    'webp_quality' => env('IMAGE_CONVERSION_WEBP_QUALITY', 82),
    'max_pixels' => env('IMAGE_CONVERSION_MAX_PIXELS', 12000000),
    'temporary_directory' => storage_path('framework/image-conversion'),
];
