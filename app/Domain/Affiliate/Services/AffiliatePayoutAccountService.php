<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        return $this->Upsert($user, AffiliatePayoutType::PROMPTPAY, ['promptpay_identifier' => $identifier], '******' . substr($identifier, -4), $correlation_id);
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
        ], '******' . substr($account_number, -4), $correlation_id);
    }

    private function Upsert(User $user, AffiliatePayoutType $type, array $details, string $masked_label, string $correlation_id): array
    {
        $this->feature_gate->AssertEnabled('wallet_withdrawals');
        $this->configuration_service->AssertAffiliateWritesAvailable();
        $profile = $user->AffiliateProfile()->first();
        if (!$profile instanceof AffiliateProfile || $profile->status !== AffiliateProfileStatus::ACTIVE) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        $account = DB::transaction(function () use ($profile, $type, $details, $masked_label): AffiliatePayoutAccount {
            return AffiliatePayoutAccount::updateOrCreate(
                ['affiliate_profile_id' => $profile->id, 'payout_type' => $type],
                ['details_encrypted' => $details, 'masked_label' => $masked_label, 'is_active' => true],
            );
        });
        $this->audit_service->Record('payout-account.upsert', 'affiliate_payout_account', $account->id, $user, [], ['payout_type' => $type->value, 'masked_label' => $masked_label], null, 'SUCCESS', null, $correlation_id);

        return [
            'id' => $account->id,
            'payout_type' => $type->value,
            'masked_label' => $masked_label,
            'is_active' => (bool) $account->is_active,
            'created_at' => $account->created_at?->toIso8601String(),
            'updated_at' => $account->updated_at?->toIso8601String(),
        ];
    }
}
