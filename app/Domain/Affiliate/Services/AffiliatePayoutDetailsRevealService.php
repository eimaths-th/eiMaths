<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Models\User;

class AffiliatePayoutDetailsRevealService
{
    public function __construct(private readonly AffiliateAuditService $audit_service)
    {
    }

    public function Reveal(AffiliateWithdrawalRequest $withdrawal, User $actor, string $correlation_id): array
    {
        if ($withdrawal->status !== AffiliateWithdrawalStatus::APPROVED) {
            throw AffiliateDomainException::StateConflict('Withdrawal is not approved for payout review.');
        }

        try {
            $payout_type = $withdrawal->payout_type;
            $snapshot = $withdrawal->payout_snapshot_encrypted;
        } catch (\Throwable) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }

        if (!$payout_type instanceof AffiliatePayoutType) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout type is invalid.');
        }

        $withdrawal->loadMissing('AffiliatePayoutAccount');
        $payout_account = $withdrawal->AffiliatePayoutAccount;
        if ($payout_account !== null && (int) $payout_account->affiliate_profile_id !== (int) $withdrawal->affiliate_profile_id) {
            throw AffiliateDomainException::OwnershipDenied();
        }
        if ($payout_account !== null && $payout_account->payout_type !== $payout_type) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout type is inconsistent.');
        }

        $details = $this->TransferDetails($payout_type, $snapshot);
        $this->audit_service->Record(
            'withdrawal.payout-details.view',
            'affiliate_withdrawal_request',
            $withdrawal->id,
            $actor,
            [
                'status' => $withdrawal->status->value,
                'payout_type' => $payout_type->value,
                'affiliate_profile_id' => $withdrawal->affiliate_profile_id,
            ],
            [
                'outcome' => 'REVEALED',
                'payout_type' => $payout_type->value,
            ],
            $withdrawal->amount_minor,
            'SUCCESS',
            null,
            $correlation_id,
        );

        return [
            'payout_type' => $payout_type->value,
            'details' => $details,
        ];
    }

    private function TransferDetails(AffiliatePayoutType $payout_type, mixed $snapshot): array
    {
        if (!is_array($snapshot)) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }

        return match ($payout_type) {
            AffiliatePayoutType::PROMPTPAY => $this->PromptPayDetails($snapshot),
            AffiliatePayoutType::BANK => $this->BankDetails($snapshot),
        };
    }

    private function PromptPayDetails(array $snapshot): array
    {
        $identifier = $snapshot['promptpay_identifier'] ?? null;
        if (!is_string($identifier)) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }
        $identifier = trim($identifier);
        if (!preg_match('/\A[0-9]{10}([0-9]{3})?\z/', $identifier)) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }

        return ['promptpay_identifier' => $identifier];
    }

    private function BankDetails(array $snapshot): array
    {
        $bank_name = $snapshot['bank_name'] ?? null;
        $account_name = $snapshot['account_name'] ?? null;
        $account_number = $snapshot['account_number'] ?? null;
        if (!is_string($bank_name) || !is_string($account_name) || !is_string($account_number)) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }
        $bank_name = trim($bank_name);
        $account_name = trim($account_name);
        $account_number = preg_replace('/[\s-]+/', '', trim($account_number));
        if ($bank_name === '' || $account_name === '' || $account_number === '' || !preg_match('/\A[0-9]{6,20}\z/', $account_number)) {
            throw AffiliateDomainException::StateConflict('Withdrawal payout details are unavailable.');
        }

        return [
            'bank_name' => $bank_name,
            'account_name' => $account_name,
            'account_number' => $account_number,
        ];
    }
}
