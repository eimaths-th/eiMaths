<?php

namespace App\Domain\CreditNote\Exceptions;

use RuntimeException;

final class CreditNoteDomainException extends RuntimeException
{
    public function __construct(
        private readonly string $reason,
        private readonly string $error_code,
        private readonly int $status_code = 422,
        private readonly string $translation_key = 'credit-note.errors.domain',
    ) {
        parent::__construct($reason);
    }

    public function Reason(): string
    {
        return $this->reason;
    }

    public function ErrorCode(): string
    {
        return $this->error_code;
    }

    public function StatusCode(): int
    {
        return $this->status_code;
    }

    public function TranslationKey(): string
    {
        return $this->translation_key;
    }

    public function SafeTranslationKey(): string
    {
        return $this->translation_key;
    }

    public static function Disabled(): self
    {
        return self::For('Credit Note is disabled.', 'CREDIT_NOTE_DISABLED', 404, 'credit-note.errors.disabled');
    }

    public static function FinancialActionsDisabled(): self
    {
        return self::For('Credit Note financial actions are disabled.', 'CREDIT_NOTE_DISABLED', 409, 'credit-note.errors.disabled');
    }

    public static function SourceIncomplete(string $reason = 'The receipt source is incomplete.'): self
    {
        return self::For($reason, 'CREDIT_NOTE_SOURCE_INCOMPLETE', 409, 'credit-note.errors.source_incomplete');
    }

    public static function SourceTotalMismatch(): self
    {
        return self::For('The receipt source total does not reconcile.', 'CREDIT_NOTE_SOURCE_TOTAL_MISMATCH', 409, 'credit-note.errors.source_total_mismatch');
    }

    public static function DraftExists(): self
    {
        return self::For('An active Credit Note Draft already exists for this receipt.', 'CREDIT_NOTE_DRAFT_EXISTS', 409, 'credit-note.errors.draft_exists');
    }

    public static function FullyCredited(): self
    {
        return self::For('The receipt has no remaining creditable value.', 'CREDIT_NOTE_FULLY_CREDITED', 409, 'credit-note.errors.fully_credited');
    }

    public static function StaleDraft(): self
    {
        return self::For('The Draft was changed by another operation.', 'CREDIT_NOTE_STALE_DRAFT', 409, 'credit-note.errors.stale_draft');
    }

    public static function SourceChanged(): self
    {
        return self::For('The receipt source changed after this Draft was created.', 'CREDIT_NOTE_SOURCE_CHANGED', 409, 'credit-note.errors.source_changed');
    }

    public static function UnknownLineType(): self
    {
        return self::For('A financially relevant receipt line has an unknown type.', 'CREDIT_NOTE_UNKNOWN_LINE_TYPE', 409, 'credit-note.errors.unknown_line_type');
    }

    public static function OverCredit(): self
    {
        return self::For('The requested credit exceeds the remaining amount.', 'CREDIT_NOTE_OVER_CREDIT', 409, 'credit-note.errors.over_credit');
    }

    public static function InvalidState(): self
    {
        return self::For('The Credit Note is not in a state that permits this action.', 'CREDIT_NOTE_INVALID_STATE', 409, 'credit-note.errors.invalid_state');
    }

    public static function InvalidEventDate(): self
    {
        return self::For('The adjustment event date is not permitted.', 'CREDIT_NOTE_INVALID_EVENT_DATE', 422, 'credit-note.errors.invalid_event_date');
    }

    public static function BuyerDetailsIncomplete(): self
    {
        return self::For('Buyer tax details are incomplete.', 'CREDIT_NOTE_BUYER_DETAILS_INCOMPLETE', 422, 'credit-note.errors.buyer_details_incomplete');
    }

    public static function IssuerConfigurationInvalid(): self
    {
        return self::For('Credit Note issuer configuration is incomplete.', 'CREDIT_NOTE_ISSUER_CONFIGURATION_INVALID', 503, 'credit-note.errors.issuer_configuration_invalid');
    }

    public static function AffiliateAdjustmentFailed(): self
    {
        return self::For('Affiliate commission adjustment could not be completed.', 'CREDIT_NOTE_AFFILIATE_ADJUSTMENT_FAILED', 409, 'credit-note.errors.affiliate_adjustment_failed');
    }

    public static function IdempotencyConflict(): self
    {
        return self::For('The idempotency key was already used for a different operation.', 'CREDIT_NOTE_IDEMPOTENCY_CONFLICT', 409, 'credit-note.errors.idempotency_conflict');
    }

    public static function PdfGenerationFailed(): self
    {
        return self::For('The Credit Note PDF could not be generated.', 'CREDIT_NOTE_PDF_GENERATION_FAILED', 500, 'credit-note.errors.pdf_generation_failed');
    }

    public static function FinancialSourceLocked(): self
    {
        return self::For('The financial source is locked by Credit Note evidence.', 'CREDIT_NOTE_FINANCIAL_SOURCE_LOCKED', 409, 'credit-note.errors.financial_source_locked');
    }

    public static function InternalError(): self
    {
        return self::For('The Credit Note operation could not be completed.', 'CREDIT_NOTE_INTERNAL_ERROR', 500, 'credit-note.errors.internal');
    }

    private static function For(string $reason, string $error_code, int $status_code, string $translation_key): self
    {
        return new self($reason, $error_code, $status_code, $translation_key);
    }
}
