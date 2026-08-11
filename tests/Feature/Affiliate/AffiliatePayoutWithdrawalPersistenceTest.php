<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AffiliatePayoutWithdrawalPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payout_details_are_encrypted_and_withdrawal_snapshot_is_decrypted_by_cast(): void
    {
        $profile = $this->createProfile();
        $account = AffiliatePayoutAccount::create([
            'affiliate_profile_id' => $profile->id,
            'payout_type' => AffiliatePayoutType::PROMPTPAY,
            'details_encrypted' => ['promptpay_identifier' => '0812345678'],
            'masked_label' => '******5678',
            'is_active' => true,
        ]);
        $withdrawal = AffiliateWithdrawalRequest::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_payout_account_id' => $account->id,
            'payout_type' => AffiliatePayoutType::PROMPTPAY,
            'amount_minor' => 1000,
            'status' => AffiliateWithdrawalStatus::REQUESTED,
            'payout_snapshot_encrypted' => ['promptpay_identifier' => '0812345678'],
            'requested_at' => now(),
            'request_key' => 'withdrawal:request:1',
        ]);

        $raw_account = DB::table('affiliate_payout_accounts')->where('id', $account->id)->value('details_encrypted');
        $raw_withdrawal = DB::table('affiliate_withdrawal_requests')->where('id', $withdrawal->id)->value('payout_snapshot_encrypted');
        $this->assertStringNotContainsString('0812345678', $raw_account);
        $this->assertStringNotContainsString('0812345678', $raw_withdrawal);
        $this->assertSame(['promptpay_identifier' => '0812345678'], $account->fresh()->details_encrypted);
        $this->assertSame(AffiliatePayoutType::PROMPTPAY, $withdrawal->fresh()->payout_type);
        $this->assertSame(['promptpay_identifier' => '0812345678'], $withdrawal->fresh()->payout_snapshot_encrypted);
    }

    public function test_one_account_per_type_and_transition_keys_are_unique(): void
    {
        $profile = $this->createProfile();
        $attributes = [
            'affiliate_profile_id' => $profile->id,
            'payout_type' => AffiliatePayoutType::BANK,
            'details_encrypted' => ['bank_name' => 'Demo Bank', 'account_number' => '1234567890', 'account_name' => 'Demo'],
            'masked_label' => '******7890',
            'is_active' => true,
        ];
        AffiliatePayoutAccount::create($attributes);

        $this->expectException(QueryException::class);
        AffiliatePayoutAccount::create($attributes);
    }

    public function test_withdrawal_snapshot_fields_are_immutable(): void
    {
        $profile = $this->createProfile();
        $account = AffiliatePayoutAccount::create([
            'affiliate_profile_id' => $profile->id,
            'payout_type' => AffiliatePayoutType::PROMPTPAY,
            'details_encrypted' => ['promptpay_identifier' => '0812345678'],
            'masked_label' => '******5678',
            'is_active' => true,
        ]);
        $withdrawal = AffiliateWithdrawalRequest::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_payout_account_id' => $account->id,
            'payout_type' => AffiliatePayoutType::PROMPTPAY,
            'amount_minor' => 1000,
            'status' => AffiliateWithdrawalStatus::REQUESTED,
            'payout_snapshot_encrypted' => ['promptpay_identifier' => '0812345678'],
            'requested_at' => now(),
            'request_key' => 'withdrawal:request:immutable',
        ]);

        $this->expectException(LogicException::class);
        $withdrawal->amount_minor = 2000;
        $withdrawal->save();
    }

    private function createProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-PAYOUT-' . $user->id,
            'status' => 'ACTIVE',
        ]);
    }
}
