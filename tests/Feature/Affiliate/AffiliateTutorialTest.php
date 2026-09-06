<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateTutorialService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateTutorialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.features.media_library', false);
        Config::set('affiliate.features.analytics', false);
    }

    #[Test]
    public function first_dashboard_offers_auto_start_but_other_pages_do_not(): void
    {
        $this->actingAs($this->Affiliate());
        $this->get('/affiliate/dashboard')->assertOk()
            ->assertSee('data-affiliate-tutorial-start', false)
            ->assertSee('"autoStart":true', false);
        $this->get('/affiliate/payout-accounts')->assertOk()->assertSee('"autoStart":false', false);
        $this->assertDatabaseCount('affiliate_tutorial_progress', 0);
    }

    #[Test]
    public function progress_survives_navigation_and_completion_can_be_replayed(): void
    {
        $user = $this->Affiliate();
        $this->actingAs($user);
        $this->Save('start', 'welcome')->assertOk()->assertJsonPath('status', 'in_progress');
        $this->Save('advance', 'bank')->assertOk();
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"step":"bank"', false)->assertSee('"autoStart":false', false);
        $this->Save('finish', 'bank')->assertStatus(422);
        $this->Save('advance', 'done')->assertOk();
        $this->Save('finish', 'done')->assertOk()->assertJsonPath('status', 'completed');
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"autoStart":false', false);
        $this->Save('advance', 'link')->assertStatus(409);
        $this->Save('start', 'welcome')->assertOk()->assertJsonPath('step', 'welcome');
        $this->assertDatabaseCount('affiliate_tutorial_progress', 1);
        // Exploring the tour must not create financial records or payout accounts.
        $this->assertDatabaseCount('affiliate_payout_accounts', 0);
        $this->assertDatabaseCount('affiliate_withdrawal_requests', 0);
    }

    #[Test]
    public function skipping_suppresses_auto_start_only_for_the_signed_in_owner(): void
    {
        $owner = $this->Affiliate();
        $other = $this->Affiliate();
        $this->actingAs($owner)->postJson('/affiliate/tutorial/progress', [
            'version' => 1, 'action' => 'skip', 'step' => 'welcome',
            'affiliate_profile_id' => $other->AffiliateProfile->id, 'user_id' => $other->id,
        ])->assertOk();
        $this->assertDatabaseHas('affiliate_tutorial_progress', ['affiliate_profile_id' => $owner->AffiliateProfile->id, 'status' => 'skipped']);
        $this->get('/affiliate/dashboard')->assertSee('"autoStart":false', false);
        $this->actingAs($other)->get('/affiliate/dashboard')->assertSee('"autoStart":true', false);
    }

    #[Test]
    public function unapproved_and_unauthenticated_users_cannot_write_progress(): void
    {
        $this->Save('start', 'welcome')->assertUnauthorized();
        foreach ([AffiliateProfileStatus::PENDING_EMAIL, AffiliateProfileStatus::PENDING_IDENTITY, AffiliateProfileStatus::PENDING_APPROVAL, AffiliateProfileStatus::SUSPENDED] as $status) {
            $this->actingAs($this->Affiliate($status));
            $this->Save('start', 'welcome')->assertForbidden();
        }
        $this->assertDatabaseCount('affiliate_tutorial_progress', 0);
    }

    #[Test]
    public function disabled_features_and_invalid_steps_cannot_be_requested(): void
    {
        $this->actingAs($this->Affiliate());
        $service = app(AffiliateTutorialService::class);
        $keys = array_column($service->Steps(), 'key');
        $this->assertNotContains('media', $keys);
        $this->assertNotContains('dates', $keys);
        $this->Save('advance', 'not-a-step')->assertUnprocessable();
        $this->Save('advance', 'media')->assertUnprocessable();
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.features.analytics', true);
        $this->assertContains('media', array_column($service->Steps(), 'key'));
        $this->assertContains('dates', array_column($service->Steps(), 'key'));
        Config::set('affiliate.features.wallet_withdrawals', false);
        $this->Save('start', 'welcome')->assertStatus(503);
    }

    #[Test]
    public function thai_and_english_cover_every_tutorial_step_and_label(): void
    {
        $thai = require base_path('lang/th/affiliate_tutorial.php');
        $english = require base_path('lang/en/affiliate_tutorial.php');
        $this->assertSame(array_keys($thai['labels']), array_keys($english['labels']));
        $this->assertSame(array_keys($thai['steps']), array_keys($english['steps']));
        foreach ($thai['steps'] as $key => $step) {
            $this->assertSame(array_keys($step), array_keys($english['steps'][$key]));
        }
    }

    private function Save(string $action, string $step)
    {
        return $this->postJson('/affiliate/tutorial/progress', ['version' => 1, 'action' => $action, 'step' => $step]);
    }

    private function Affiliate(AffiliateProfileStatus $status = AffiliateProfileStatus::ACTIVE): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'language_code' => 'th', 'password' => Hash::make('tutorial-test')]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-TUTORIAL-' . $user->id, 'status' => $status]);

        return $user;
    }
}
