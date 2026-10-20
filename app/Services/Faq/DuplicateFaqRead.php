<?php
namespace App\Services\Faq;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;
final class DuplicateFaqRead extends RuntimeException
{
    public function __construct(UniqueConstraintViolationException $previous)
    {
        parent::__construct('Duplicate FAQ read receipt', 0, $previous);
    }
}
