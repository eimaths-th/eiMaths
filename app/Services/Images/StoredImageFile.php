<?php

namespace App\Services\Images;

final class StoredImageFile
{
    public readonly string $filename;
    public readonly string $absolute_path;
    public readonly string $relative_path;
    public readonly bool $was_converted;

    public function __construct(
        string $filename,
        string $absolute_path,
        string $relative_path,
        bool $was_converted,
    ) {
        $this->filename = $filename;
        $this->absolute_path = $absolute_path;
        $this->relative_path = $relative_path;
        $this->was_converted = $was_converted;
    }
}
