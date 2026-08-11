<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateClickTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('affiliate.features.referral_capture', true);
        Config::set('affiliate.features.click_tracking', true);
        Config::set('affiliate.visitor_hash_key', str_repeat('t', 32));
        Config::set('affiliate.allowed_destination_hosts', ['localhost']);
    }

    public function test_direct_tracking_redirects_to_root_and_records_a_click(): void
    {
        $profile = $this->CreateProfile(AffiliateProfileStatus::ACTIVE, 'AFF-TRACK-DIRECT');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->withHeader('User-Agent', 'Affiliate Test Browser')
            ->get('/r/' . $profile->referral_code . '?utm_source=ignored');

        $response->assertStatus(302)->assertRedirect('/');
        $this->assertDatabaseHas('affiliate_click_events', [
            'affiliate_profile_id' => $profile->id,
            'source_type' => 'direct',
            'destination_target' => '/',
        ]);
        $this->assertNotNull($response->getCookie(config('affiliate.cookie_name')));
    }

    public function test_media_tracking_uses_the_stored_destination(): void
    {
        $profile = $this->CreateProfile(AffiliateProfileStatus::ACTIVE, 'AFF-TRACK-MEDIA');
        $content = AffiliateContent::query()->create([
            'title' => 'Tracked media',
            'file_path' => 'affiliate/test.webp',
            'type' => 'image',
            'description' => 'Test media',
            'status' => 'enabled',
            'created_by' => $profile->user_id,
        ]);
        $content->forceFill([
            'campaign_code' => 'spring-sale',
            'destination_target' => '/courses',
        ])->save();

        $response = $this->get('/r/' . $profile->referral_code . '/spring-sale');

        $response->assertStatus(302)->assertRedirect('/courses');
        $this->assertDatabaseHas('affiliate_click_events', [
            'affiliate_profile_id' => $profile->id,
            'affiliate_content_id' => $content->id,
            'campaign_code' => 'spring-sale',
            'source_type' => 'media',
            'destination_target' => '/courses',
        ]);
    }

    public function test_invalid_or_unpublished_targets_redirect_to_root_without_tracking_or_cookie(): void
    {
        $profile = $this->CreateProfile(AffiliateProfileStatus::SUSPENDED, 'AFF-TRACK-SUSPENDED');

        $response = $this->get('/r/' . $profile->referral_code);

        $response->assertStatus(302)->assertRedirect('/');
        $this->assertSame(0, AffiliateClickEvent::query()->count());
        $this->assertNull($response->getCookie(config('affiliate.cookie_name')));
    }

    public function test_rate_limit_is_generic_and_does_not_depend_on_analytics_feature(): void
    {
        Config::set('affiliate.abuse.click_attempts_per_minute', 1);
        Config::set('affiliate.features.click_tracking', false);
        $profile = $this->CreateProfile(AffiliateProfileStatus::ACTIVE, 'AFF-TRACK-LIMIT');

        $this->get('/r/' . $profile->referral_code)->assertStatus(302);
        $limited = $this->get('/r/' . $profile->referral_code);

        $limited->assertStatus(429)
            ->assertSee('Too many requests')
            ->assertDontSee($profile->referral_code);
    }

    public function test_disabled_click_tracking_still_sets_referral_cookie_without_event(): void
    {
        Config::set('affiliate.features.click_tracking', false);
        $profile = $this->CreateProfile(AffiliateProfileStatus::ACTIVE, 'AFF-TRACK-DISABLED');

        $response = $this->get('/r/' . $profile->referral_code);

        $response->assertStatus(302)->assertRedirect('/');
        $this->assertSame(0, AffiliateClickEvent::query()->count());
        $this->assertNotNull($response->getCookie(config('affiliate.cookie_name')));
    }

    public function test_query_string_is_not_persisted_as_the_direct_destination(): void
    {
        $profile = $this->CreateProfile(AffiliateProfileStatus::ACTIVE, 'AFF-TRACK-QUERY');

        $response = $this->get('/r/' . $profile->referral_code . '?next=/private-area');

        $response->assertRedirect('/');
        $this->assertDatabaseHas('affiliate_click_events', [
            'affiliate_profile_id' => $profile->id,
            'destination_target' => '/',
        ]);
        $this->assertStringNotContainsString('private-area', (string) $response->getCookie(config('affiliate.cookie_name'))?->getValue());
    }

    private function CreateProfile(AffiliateProfileStatus $status, string $referral_code): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_code,
            'status' => $status,
        ]);
    }
}
