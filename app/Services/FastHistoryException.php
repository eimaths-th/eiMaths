<?php

namespace App\Services;

use RuntimeException;

class FastHistoryException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status_code = 422)
    {
        parent::__construct($message);
    }
}
