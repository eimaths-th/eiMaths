<?php

namespace App\Domain\Affiliate\Exceptions;

use RuntimeException;

final class AffiliateDomainException extends RuntimeException
{
    public function __construct(
        private readonly string $reason,
        private readonly string $error_code,
        private readonly int $status_code = 422,
        private readonly string $translation_key = 'affiliate.errors.domain',
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

    public static function FinancialSourceLocked(): self
    {
        return new self('Financial source is locked.', 'AFFILIATE_FINANCIAL_SOURCE_LOCKED', 409, 'affiliate.errors.financial_source_locked');
    }

    public static function StateConflict(string $reason = 'Affiliate state conflict.'): self
    {
        return new self($reason, 'AFFILIATE_STATE_CONFLICT', 409, 'affiliate.errors.state_conflict');
    }

    public static function OwnershipDenied(): self
    {
        return new self('The requested Affiliate resource is not owned by the authenticated user.', 'AFFILIATE_OWNERSHIP_DENIED', 403, 'affiliate.errors.ownership_denied');
    }

    public static function ConfigurationInvalid(string $reason = 'Affiliate configuration is invalid.'): self
    {
        return new self($reason, 'AFFILIATE_CONFIGURATION_INVALID', 503, 'affiliate.errors.configuration_invalid');
    }

    public static function InsufficientBalance(): self
    {
        return new self('Available balance is insufficient.', 'AFFILIATE_INSUFFICIENT_BALANCE', 422, 'affiliate.errors.insufficient_balance');
    }

    public static function NegativeBalanceWithdrawal(): self
    {
        return new self('Withdrawal is unavailable while the available balance is not positive.', 'AFFILIATE_NEGATIVE_BALANCE_WITHDRAWAL', 422, 'affiliate.errors.negative_balance_withdrawal');
    }

    public static function ActorSeparation(): self
    {
        return new self('The approving and paying actors must be different users.', 'AFFILIATE_ACTOR_SEPARATION', 409, 'affiliate.errors.actor_separation');
    }

    public static function IdempotencyConflict(): self
    {
        return new self('The idempotency key was already used for a different operation.', 'AFFILIATE_IDEMPOTENCY_CONFLICT', 409, 'affiliate.errors.idempotency_conflict');
    }
}
