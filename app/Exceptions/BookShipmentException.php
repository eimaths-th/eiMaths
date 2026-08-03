<?php

namespace App\Exceptions;

use RuntimeException;

class BookShipmentException extends RuntimeException
{
    public const DESTINATION_INELIGIBLE = 'DESTINATION_INELIGIBLE';
    public const SELECTION_INVALID = 'SELECTION_INVALID';
    public const SOURCE_INELIGIBLE = 'SOURCE_INELIGIBLE';
    public const SOURCE_CONFLICT = 'SOURCE_CONFLICT';
    public const STATE_CONFLICT = 'STATE_CONFLICT';
    public const CANCELLATION_REASON_INVALID = 'CANCELLATION_REASON_INVALID';
    public const ITEM_SET_INVALID = 'ITEM_SET_INVALID';
    public const ISSUE_NOTE_INVALID = 'ISSUE_NOTE_INVALID';

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
            self::DESTINATION_INELIGIBLE => 'BOOK_SHIPMENT_DESTINATION_INELIGIBLE',
            self::SELECTION_INVALID => 'BOOK_SHIPMENT_SELECTION_INVALID',
            self::SOURCE_INELIGIBLE => 'BOOK_SHIPMENT_SOURCE_INELIGIBLE',
            self::SOURCE_CONFLICT => 'BOOK_SHIPMENT_SOURCE_CONFLICT',
            self::STATE_CONFLICT => 'BOOK_SHIPMENT_STATE_CONFLICT',
            self::CANCELLATION_REASON_INVALID => 'BOOK_SHIPMENT_CANCELLATION_REASON_INVALID',
            self::ITEM_SET_INVALID => 'BOOK_SHIPMENT_ITEM_SET_INVALID',
            self::ISSUE_NOTE_INVALID => 'BOOK_SHIPMENT_ISSUE_NOTE_INVALID',
            default => 'BOOK_SHIPMENT_STATE_CONFLICT',
        };
    }

    public function StatusCode(): int
    {
        return in_array($this->reason, [
            self::DESTINATION_INELIGIBLE,
            self::SELECTION_INVALID,
            self::SOURCE_INELIGIBLE,
            self::CANCELLATION_REASON_INVALID,
            self::ITEM_SET_INVALID,
            self::ISSUE_NOTE_INVALID,
        ], true) ? 422 : 409;
    }
}
