<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\ValueObjects\Money;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateWithdrawalService
{
    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateWalletService $wallet_service,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function RequestWithdrawal(User $owner, string $amount, int $payout_account_id, string $request_key, string $correlation_id): AffiliateWithdrawalRequest
    {
        $this->feature_gate->AssertEnabled('wallet_withdrawals');
        $this->configuration_service->AssertAffiliateWritesAvailable();
        $profile = $this->ActiveProfile($owner);
        $amount_minor = Money::FromDecimal($amount, true)->Minor();
        if ($amount_minor < $this->configuration_service->MinimumWithdrawalMinor()) {
            throw AffiliateDomainException::InsufficientBalance();
        }

        $existing = AffiliateWithdrawalRequest::query()->where('request_key', $request_key)->first();
        if ($existing !== null) {
            if ((int) $existing->affiliate_profile_id !== (int) $profile->id) {
                throw AffiliateDomainException::IdempotencyConflict();
            }
            return $existing;
        }

        return DB::transaction(function () use ($profile, $owner, $amount_minor, $payout_account_id, $request_key, $correlation_id): AffiliateWithdrawalRequest {
            $account = AffiliatePayoutAccount::query()
                ->whereKey($payout_account_id)
                ->where('affiliate_profile_id', $profile->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();
            if ($account === null) {
                throw AffiliateDomainException::OwnershipDenied();
            }
            $wallet = AffiliateWalletBalance::query()->where('affiliate_profile_id', $profile->id)->lockForUpdate()->first();
            if ($wallet === null || $wallet->available_balance_minor <= 0 || $wallet->available_balance_minor < $amount_minor) {
                throw AffiliateDomainException::NegativeBalanceWithdrawal();
            }

            $withdrawal = AffiliateWithdrawalRequest::create([
                'affiliate_profile_id' => $profile->id,
                'affiliate_payout_account_id' => $account->id,
                'payout_type' => $account->payout_type,
                'amount_minor' => $amount_minor,
                'status' => AffiliateWithdrawalStatus::REQUESTED,
                'payout_snapshot_encrypted' => $account->details_encrypted,
                'requested_at' => now(),
                'request_key' => $request_key,
            ]);
            $this->wallet_service->ApplyEntry($profile, AffiliateLedgerEntryType::WITHDRAWAL_RESERVE, -$amount_minor, $amount_minor, 'affiliate_withdrawal', $withdrawal->id, 'affiliate:withdrawal-reserve:' . $withdrawal->id, $owner, 'WITHDRAWAL_RESERVE', $correlation_id);

            return $withdrawal->fresh();
        });
    }

    public function Approve(AffiliateWithdrawalRequest $withdrawal, User $actor, string $approval_key, string $correlation_id): AffiliateWithdrawalRequest
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        return DB::transaction(function () use ($withdrawal, $actor, $approval_key): AffiliateWithdrawalRequest {
            $locked = AffiliateWithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === AffiliateWithdrawalStatus::APPROVED && $locked->approval_key === $approval_key) {
                return $locked;
            }
            if ($locked->status !== AffiliateWithdrawalStatus::REQUESTED) {
                throw AffiliateDomainException::StateConflict('Withdrawal is not awaiting approval.');
            }
            $locked->forceFill(['status' => AffiliateWithdrawalStatus::APPROVED, 'approved_by' => $actor->id, 'approved_at' => now(), 'approval_key' => $approval_key])->save();
            return $locked->fresh();
        });
    }

    public function Pay(AffiliateWithdrawalRequest $withdrawal, User $actor, string $payment_key, string $correlation_id): AffiliateWithdrawalRequest
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        return DB::transaction(function () use ($withdrawal, $actor, $payment_key, $correlation_id): AffiliateWithdrawalRequest {
            $locked = AffiliateWithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === AffiliateWithdrawalStatus::PAID && $locked->payment_key === $payment_key) {
                return $locked;
            }
            if ($locked->status !== AffiliateWithdrawalStatus::APPROVED) {
                throw AffiliateDomainException::StateConflict('Withdrawal is not approved for payment.');
            }
            $self_payment = (int) $locked->approved_by === (int) $actor->id;
            if ($self_payment && !$actor->can('affiliate-withdrawal-pay-self')) {
                throw AffiliateDomainException::ActorSeparation();
            }
            $this->wallet_service->ApplyEntry($locked->AffiliateProfile()->firstOrFail(), AffiliateLedgerEntryType::WITHDRAWAL_PAID, 0, -$locked->amount_minor, 'affiliate_withdrawal', $locked->id, 'affiliate:withdrawal-paid:' . $locked->id, $actor, 'WITHDRAWAL_PAID', $correlation_id);
            $locked->forceFill(['status' => AffiliateWithdrawalStatus::PAID, 'paid_by' => $actor->id, 'paid_at' => now(), 'payment_key' => $payment_key])->save();
            if ($self_payment) {
                $this->audit_service->Record(
                    operation: 'withdrawal.self-pay',
                    subject_type: 'affiliate_withdrawal_request',
                    subject_id: $locked->id,
                    actor: $actor,
                    prior_state: [
                        'status' => AffiliateWithdrawalStatus::APPROVED->value,
                        'approved_by' => $locked->approved_by,
                    ],
                    resulting_state: [
                        'status' => AffiliateWithdrawalStatus::PAID->value,
                        'approved_by' => $locked->approved_by,
                        'paid_by' => $actor->id,
                        'self_payment' => true,
                    ],
                    amount_minor: $locked->amount_minor,
                    outcome: 'SUCCESS',
                    error_code: null,
                    correlation_id: $correlation_id,
                );
            }
            return $locked->fresh();
        });
    }

    public function Cancel(AffiliateWithdrawalRequest $withdrawal, User $actor, string $reason, string $closure_key, string $correlation_id): AffiliateWithdrawalRequest
    {
        return $this->Close($withdrawal, $actor, $reason, $closure_key, AffiliateWithdrawalStatus::CANCELLED, $correlation_id);
    }

    public function Reject(AffiliateWithdrawalRequest $withdrawal, User $actor, string $reason, string $closure_key, string $correlation_id): AffiliateWithdrawalRequest
    {
        return $this->Close($withdrawal, $actor, $reason, $closure_key, AffiliateWithdrawalStatus::REJECTED, $correlation_id);
    }

    private function Close(AffiliateWithdrawalRequest $withdrawal, User $actor, string $reason, string $closure_key, AffiliateWithdrawalStatus $status, string $correlation_id): AffiliateWithdrawalRequest
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (trim($reason) === '') {
            throw AffiliateDomainException::StateConflict('A withdrawal closure reason is required.');
        }
        return DB::transaction(function () use ($withdrawal, $actor, $reason, $closure_key, $status, $correlation_id): AffiliateWithdrawalRequest {
            $locked = AffiliateWithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, [AffiliateWithdrawalStatus::CANCELLED, AffiliateWithdrawalStatus::REJECTED], true) && $locked->closure_key === $closure_key) {
                return $locked;
            }
            if (!in_array($locked->status, [AffiliateWithdrawalStatus::REQUESTED, AffiliateWithdrawalStatus::APPROVED], true)) {
                throw AffiliateDomainException::StateConflict('Withdrawal cannot be closed in its current state.');
            }
            $this->wallet_service->ApplyEntry($locked->AffiliateProfile()->firstOrFail(), AffiliateLedgerEntryType::WITHDRAWAL_RELEASE, $locked->amount_minor, -$locked->amount_minor, 'affiliate_withdrawal', $locked->id, 'affiliate:withdrawal-release:' . $locked->id, $actor, 'WITHDRAWAL_RELEASE', $correlation_id);
            $locked->forceFill(['status' => $status, 'closed_by' => $actor->id, 'closed_at' => now(), 'decision_reason' => trim($reason), 'closure_key' => $closure_key])->save();
            return $locked->fresh();
        });
    }

    private function ActiveProfile(User $owner): AffiliateProfile
    {
        $profile = $owner->AffiliateProfile()->first();
        if (!$profile instanceof AffiliateProfile || $profile->status !== AffiliateProfileStatus::ACTIVE) {
            throw AffiliateDomainException::OwnershipDenied();
        }
        return $profile;
    }
}
