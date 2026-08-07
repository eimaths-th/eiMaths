<?php

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\ValueObjects\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('affiliate_profiles')) {
            return;
        }

        DB::transaction(function (): void {
            $users = DB::table('users')->select('id', 'email', 'name', 'referral', 'affiliate_request')->get();
            foreach ($users as $user) {
                $is_affiliate = (int) ($user->affiliate_request ?? 0) === 1;
                if (!$is_affiliate && Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                    try {
                        $is_affiliate = DB::table('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('model_has_roles.model_type', 'App\\Models\\User')
                            ->where('model_has_roles.model_id', $user->id)
                            ->where('roles.name', 'Affiliate-user')
                            ->exists();
                    } catch (Throwable) {
                        $is_affiliate = false;
                    }
                }
                if (!$is_affiliate) {
                    continue;
                }

                $profile = AffiliateProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'referral_code' => $this->ReferralCode($user->referral, (int) $user->id),
                        'status' => AffiliateProfileStatus::ACTIVE,
                        'migration_status' => 'MIGRATED',
                    ],
                );
                if ($profile->migration_status !== 'MIGRATED') {
                    $profile->forceFill(['status' => AffiliateProfileStatus::ACTIVE, 'migration_status' => 'MIGRATED'])->save();
                }

                $this->MigrateWallet($profile, (int) $user->id);
                $this->MigrateBank($profile, (int) $user->id);
                $this->MigratePendingWithdrawals($profile, (int) $user->id);
            }
        });
    }

    public function down(): void
    {
        // Legacy migration is intentionally non-destructive and has no rollback path.
    }

    private function MigrateWallet(AffiliateProfile $profile, int $user_id): void
    {
        if (!Schema::hasTable('affiliate_user_wallets')) {
            return;
        }
        $wallets = DB::table('affiliate_user_wallets')->where('user_id', $user_id)->orderBy('id')->get();
        foreach ($wallets as $wallet) {
            if (AffiliateLedgerEntry::query()->where('idempotency_key', 'affiliate:migration:wallet:' . $wallet->id)->exists()) {
                continue;
            }
            $amount_minor = $this->Minor((string) ($wallet->amount ?? '0'));
            $entry = AffiliateLedgerEntry::create([
                'affiliate_profile_id' => $profile->id,
                'entry_type' => AffiliateLedgerEntryType::OPENING_BALANCE,
                'available_delta_minor' => $amount_minor,
                'reserved_delta_minor' => 0,
                'balance_available_after_minor' => $amount_minor,
                'balance_reserved_after_minor' => 0,
                'source_type' => 'legacy_affiliate_user_wallets',
                'source_id' => $wallet->id,
                'idempotency_key' => 'affiliate:migration:wallet:' . $wallet->id,
                'actor_id' => null,
                'reason_code' => 'OPENING_BALANCE_MIGRATION',
                'occurred_at' => now(),
            ]);
            DB::table('affiliate_wallet_balances')->updateOrInsert(
                ['affiliate_profile_id' => $profile->id],
                ['available_balance_minor' => $amount_minor, 'reserved_balance_minor' => 0, 'last_ledger_entry_id' => $entry->id, 'created_at' => now(), 'updated_at' => now()],
            );
            if (filled($wallet->paypal_account ?? null)) {
                $this->Issue($wallet->id, 'UNSUPPORTED_LEGACY_PAYOUT', ['payout_label' => 'legacy_paypal_account']);
            }
        }
    }

    private function MigrateBank(AffiliateProfile $profile, int $user_id): void
    {
        if (!Schema::hasTable('affiliate_user_banking')) {
            return;
        }
        $bank = DB::table('affiliate_user_banking')->where('user_id', $user_id)->orderByDesc('id')->first();
        if ($bank === null || !$this->ValidDigits((string) $bank->account_number)) {
            return;
        }
        AffiliatePayoutAccount::query()->firstOrCreate(
            ['affiliate_profile_id' => $profile->id, 'payout_type' => AffiliatePayoutType::BANK],
            [
                'details_encrypted' => ['bank_name' => (string) $bank->bank_name, 'account_number' => (string) $bank->account_number, 'account_name' => (string) $bank->account_name],
                'masked_label' => '******' . substr((string) $bank->account_number, -4),
                'is_active' => true,
            ],
        );
    }

    private function MigratePendingWithdrawals(AffiliateProfile $profile, int $user_id): void
    {
        if (!Schema::hasTable('affiliate_withdraws')) {
            return;
        }
        $rows = DB::table('affiliate_withdraws')->where('user_id', $user_id)->where('status', 0)->orderBy('id')->get();
        foreach ($rows as $row) {
            if ((int) $row->payment_type !== 1) {
                $this->Issue($row->id, 'UNSUPPORTED_LEGACY_WITHDRAWAL', ['payment_type' => $row->payment_type]);
                continue;
            }
            $account = AffiliatePayoutAccount::query()->where('affiliate_profile_id', $profile->id)->where('payout_type', AffiliatePayoutType::BANK)->first();
            if ($account === null) {
                $this->Issue($row->id, 'LEGACY_WITHDRAWAL_MISSING_PAYOUT', []);
                continue;
            }
            AffiliateWithdrawalRequest::query()->firstOrCreate(
                ['request_key' => 'affiliate:migration:withdrawal:' . $row->id],
                [
                    'affiliate_profile_id' => $profile->id,
                    'affiliate_payout_account_id' => $account->id,
                    'payout_type' => AffiliatePayoutType::BANK,
                    'amount_minor' => $this->Minor((string) ($row->withdraw_amount ?? '0')),
                    'status' => AffiliateWithdrawalStatus::REQUESTED,
                    'payout_snapshot_encrypted' => $account->details_encrypted,
                    'requested_at' => $row->request_date ?: now(),
                ],
            );
        }
    }

    private function Issue(int $source_id, string $code, array $evidence): void
    {
        AffiliateAuditLog::query()->firstOrCreate(
            ['operation' => 'legacy.migration.issue', 'subject_type' => 'legacy_affiliate', 'subject_id' => $source_id, 'error_code' => $code],
            ['correlation_id' => 'affiliate-migration', 'prior_state' => [], 'resulting_state' => $evidence, 'amount_minor' => null, 'outcome' => 'REVIEW_REQUIRED', 'occurred_at' => now()],
        );
    }

    private function ReferralCode(?string $value, int $user_id): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return 'legacy-' . $user_id;
        }
        if (DB::table('affiliate_profiles')->where('referral_code', $candidate)->exists()) {
            return 'legacy-' . $user_id;
        }
        return $candidate;
    }

    private function ValidDigits(string $value): bool
    {
        return preg_match('/\\A[0-9]{6,20}\\z/', preg_replace('/[\\s-]+/', '', trim($value))) === 1;
    }

    private function Minor(string $value): int
    {
        return Money::FromDecimal(preg_replace('/\\s+/', '', trim($value)) ?: '0')->Minor();
    }
};
