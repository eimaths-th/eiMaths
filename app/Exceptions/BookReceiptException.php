<?php

namespace App\Exceptions;

use RuntimeException;

class BookReceiptException extends RuntimeException
{
    public const ALREADY_RECEIVED = 'ALREADY_RECEIVED';
    public const NOT_RECEIVED = 'NOT_RECEIVED';
    public const INELIGIBLE = 'INELIGIBLE';
    public const SHIPMENT_BLOCKED = 'SHIPMENT_BLOCKED';

    public function __construct(private readonly string $reason)
    {
        parent::__construct($reason);
    }

    public function Reason(): string
    {
        return $this->reason;
    }

    public function ErrorCode(): string
    {
        return match ($this->reason) {
            self::INELIGIBLE => 'BOOK_RECEIPT_INELIGIBLE',
            self::SHIPMENT_BLOCKED => 'BOOK_RECEIPT_SHIPMENT_BLOCKED',
            default => 'BOOK_RECEIPT_STATE_CONFLICT',
        };
    }

    public function StatusCode(): int
    {
        return $this->reason === self::INELIGIBLE ? 422 : 409;
    }
}
