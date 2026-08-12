<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.media_library', true);
    }

    public function test_active_affiliate_receives_only_current_enabled_media_with_server_tracking_links(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-MEDIA-LIBRARY',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        $current = $this->CreateContent($user, 'Spring & Summer', 'spring-library', 'enabled');
        $this->CreateContent($user, 'Disabled', 'disabled-library', 'disabled');
        $archived = $this->CreateContent($user, 'Archived', 'archived-library', 'enabled');
        $archived->forceFill(['archived_at' => now(), 'deleted_at' => now()])->saveQuietly();

        $response = $this->actingAs($user)->get(route('affiliate.media.index'));

        $response->assertOk()
            ->assertViewIs('affiliate.media.index')
            ->assertSee('Spring &amp; Summer', false)
            ->assertDontSee('data-media-copy-caption', false)
            ->assertDontSee('data-media-copy-link', false)
            ->assertSee('data-media-share', false)
            ->assertSee('id="affiliate-media-tracking-url-' . $current->id . '"', false)
            ->assertSee('value="' . route('affiliate.tracking.media', [$profile->referral_code, $current->campaign_code]) . '"', false)
            ->assertDontSee('disabled-library')
            ->assertDontSee('archived-library');
    }

    public function test_search_and_type_filters_are_bounded_and_feature_gate_is_enforced(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-MEDIA-FILTER',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        $this->CreateContent($user, 'Video lesson', 'video-lesson', 'enabled', 'video');
        $this->CreateContent($user, 'Image lesson', 'image-lesson', 'enabled', 'image');

        $this->actingAs($user)->get(route('affiliate.media.index', ['type' => 'video', 'search' => 'Video']))
            ->assertOk()
            ->assertSee('video-lesson')
            ->assertDontSee('image-lesson');

        Config::set('affiliate.features.media_library', false);
        $this->actingAs($user)->get(route('affiliate.media.index'))->assertStatus(503);
    }

    private function CreateContent(User $user, string $title, string $campaign_code, string $status, string $type = 'image'): AffiliateContent
    {
        $content = AffiliateContent::query()->create([
            'title' => $title,
            'file_path' => 'uploads/affiliate/' . $type . '/library-file.' . ($type === 'video' ? 'mp4' : 'webp'),
            'type' => $type,
            'description' => 'Safe caption',
            'status' => $status,
            'created_by' => $user->id,
        ]);
        $content->forceFill([
            'campaign_code' => $campaign_code,
            'destination_target' => '/courses',
        ])->save();

        return $content;
    }
}
