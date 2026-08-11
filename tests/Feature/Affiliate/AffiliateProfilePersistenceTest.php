<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateProfilePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_persists_status_cast_and_user_relationship(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-TEST-001',
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);

        $this->assertSame(AffiliateProfileStatus::PENDING_APPROVAL, $profile->fresh()->status);
        $this->assertSame($profile->id, $user->fresh()->AffiliateProfile->id);
    }

    public function test_user_and_referral_code_are_independently_unique(): void
    {
        $first_user = User::factory()->create(['password' => Hash::make('secret')]);
        $second_user = User::factory()->create(['password' => Hash::make('secret')]);

        AffiliateProfile::create([
            'user_id' => $first_user->id,
            'referral_code' => 'AFF-UNIQUE-001',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        $this->expectException(QueryException::class);
        AffiliateProfile::create([
            'user_id' => $second_user->id,
            'referral_code' => 'AFF-UNIQUE-001',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }

    public function test_profile_ownership_restricts_user_deletion(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-RESTRICT-001',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        $this->expectException(QueryException::class);
        $user->delete();
    }

    public function test_lifecycle_actor_deletion_nulls_actor_but_keeps_evidence(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ACTOR-001',
            'status' => AffiliateProfileStatus::SUSPENDED,
            'suspended_by' => $actor->id,
            'suspended_at' => now(),
            'status_reason' => 'Policy review',
        ]);

        $actor->delete();

        $profile = $profile->fresh();
        $this->assertNull($profile->suspended_by);
        $this->assertNotNull($profile->suspended_at);
        $this->assertSame('Policy review', $profile->status_reason);
    }
}
