<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use App\Notifications\AffiliatePayoutAccountChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AffiliatePayoutAccountService
{
    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateAuditService $audit_service,
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function UpsertPromptPay(User $user, array $input, string $correlation_id): array
    {
        $identifier = preg_replace('/[\s-]+/', '', trim((string) ($input['promptpay_identifier'] ?? '')));
        if (!preg_match('/\A[0-9]{10}([0-9]{3})?\z/', $identifier)) {
            throw AffiliateDomainException::StateConflict('PromptPay identifier must contain 10 or 13 ASCII digits.');
        }

        return $this->Upsert($user, AffiliatePayoutType::PROMPTPAY, ['promptpay_identifier' => $identifier], '******' . substr($identifier, -4), $correlation_id, (string) ($input['current_password'] ?? ''));
    }

    public function UpsertBank(User $user, array $input, string $correlation_id): array
    {
        $bank_name = trim((string) ($input['bank_name'] ?? ''));
        $account_name = trim((string) ($input['account_name'] ?? ''));
        $account_number = preg_replace('/[\s-]+/', '', trim((string) ($input['account_number'] ?? '')));
        if ($bank_name === '' || $account_name === '' || strlen($bank_name) > 255 || strlen($account_name) > 255 || !preg_match('/\A[0-9]{6,20}\z/', $account_number)) {
            throw AffiliateDomainException::StateConflict('Bank payout details are invalid.');
        }

        return $this->Upsert($user, AffiliatePayoutType::BANK, [
            'bank_name' => $bank_name,
            'account_number' => $account_number,
            'account_name' => $account_name,
        ], '******' . substr($account_number, -4), $correlation_id, (string) ($input['current_password'] ?? ''));
    }

    private function Upsert(User $user, AffiliatePayoutType $type, array $details, string $masked_label, string $correlation_id, string $current_password): array
    {
        $this->feature_gate->AssertEnabled('wallet_withdrawals');
        $this->configuration_service->AssertAffiliateWritesAvailable();
        $profile = $user->AffiliateProfile()->first();
        if (!$profile instanceof AffiliateProfile || $profile->status !== AffiliateProfileStatus::ACTIVE) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        $account = DB::transaction(function () use ($profile, $user, $type, $details, $masked_label, $current_password): AffiliatePayoutAccount {
            $account = AffiliatePayoutAccount::query()
                ->where('affiliate_profile_id', $profile->id)
                ->where('payout_type', $type)
                ->lockForUpdate()
                ->first();
            $details_changed = $account !== null && $account->details_encrypted !== $details;
            if ($details_changed && !Hash::check($current_password, (string) $user->password)) {
                throw AffiliateDomainException::PasswordConfirmationRequired();
            }

            if ($account === null) {
                return AffiliatePayoutAccount::create([
                    'affiliate_profile_id' => $profile->id,
                    'payout_type' => $type,
                    'details_encrypted' => $details,
                    'masked_label' => $masked_label,
                    'is_active' => true,
                    'payout_cooldown_until' => null,
                ]);
            }

            $account->forceFill([
                'details_encrypted' => $details,
                'masked_label' => $masked_label,
                'is_active' => true,
                'payout_cooldown_until' => $details_changed ? now()->addHours(24) : $account->payout_cooldown_until,
            ])->save();
            if ($details_changed) {
                $user->notify(new AffiliatePayoutAccountChangedNotification($type, $account->payout_cooldown_until));
            }

            return $account->fresh();
        });
        $this->audit_service->Record('payout-account.upsert', 'affiliate_payout_account', $account->id, $user, [], ['payout_type' => $type->value, 'masked_label' => $masked_label], null, 'SUCCESS', null, $correlation_id);

        return [
            'id' => $account->id,
            'payout_type' => $type->value,
            'masked_label' => $masked_label,
            'is_active' => (bool) $account->is_active,
            'payout_cooldown_until' => $account->payout_cooldown_until?->toIso8601String(),
            'created_at' => $account->created_at?->toIso8601String(),
            'updated_at' => $account->updated_at?->toIso8601String(),
        ];
    }
}
