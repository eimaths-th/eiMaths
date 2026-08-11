<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateTrafficClassifier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateTrafficClassifierTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ClassifiesKnownBotsCaseInsensitively(): void
    {
        Config::set('affiliate.abuse.known_bot_tokens', ['crawler-token']);
        $profile = $this->CreateProfile();
        $request = Request::create('/r/AFF-001', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'My CRAWLER-TOKEN agent']);

        $this->assertSame(
            AffiliateTrafficClassifier::REASON_KNOWN_BOT,
            app(AffiliateTrafficClassifier::class)->RejectionReason($request, $profile),
        );
    }

    #[Test]
    public function RejectsOwnerButAcceptsAnotherAuthenticatedUserAndAnonymousTraffic(): void
    {
        $owner = User::factory()->create(['password' => Hash::make('owner-password')]);
        $profile = AffiliateProfile::create([
            'user_id' => $owner->id,
            'referral_code' => 'AFF-CLASS-' . $owner->id,
            'status' => 'ACTIVE',
        ]);
        $request = Request::create('/r/' . $profile->referral_code, 'GET');

        $this->actingAs($owner);
        $this->assertSame(
            AffiliateTrafficClassifier::REASON_AUTHENTICATED_OWNER,
            app(AffiliateTrafficClassifier::class)->RejectionReason($request, $profile),
        );

        $other = User::factory()->create(['password' => Hash::make('other-password')]);
        $this->actingAs($other);
        $this->assertNull(app(AffiliateTrafficClassifier::class)->RejectionReason($request, $profile));

        auth()->logout();
        $this->assertNull(app(AffiliateTrafficClassifier::class)->RejectionReason($request, $profile));
    }

    private function CreateProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('classifier-password')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-CLASS-' . $user->id,
            'status' => 'ACTIVE',
        ]);
    }
}
