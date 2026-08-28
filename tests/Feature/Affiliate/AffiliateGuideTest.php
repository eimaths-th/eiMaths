<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    #[Test]
    public function active_affiliate_can_read_the_guide_and_open_the_source_document(): void
    {
        $user = $this->CreateAffiliate(AffiliateProfileStatus::ACTIVE);

        $page_response = $this->actingAs($user)->get('/affiliate/guide');

        $page_response->assertOk()
            ->assertViewIs('affiliate.guide.index')
            ->assertSee(trans('affiliate.guide.title', [], app()->getLocale()))
            ->assertSee('href="' . route('affiliate.guide.document') . '"', false)
            ->assertSee('iframe', false);

        $document_response = $this->actingAs($user)->get('/affiliate/guide/document');

        $document_response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function inactive_affiliate_is_redirected_away_from_the_guide_and_document(): void
    {
        $user = $this->CreateAffiliate(AffiliateProfileStatus::SUSPENDED);

        $this->actingAs($user)
            ->get('/affiliate/guide')
            ->assertRedirect(route('affiliate.waiting'));

        $this->actingAs($user)
            ->get('/affiliate/guide/document')
            ->assertRedirect(route('affiliate.waiting'));
    }

    #[Test]
    public function affiliate_finance_navigation_links_to_the_guide(): void
    {
        $user = $this->CreateAffiliate(AffiliateProfileStatus::ACTIVE);

        $response = $this->actingAs($user)->get('/affiliate/guide');

        $response->assertSee('href="' . route('affiliate.guide.index') . '"', false)
            ->assertSee(trans('affiliate.navigation.guide', [], app()->getLocale()));

        $dashboard_response = $this->actingAs($user)->get(route('affiliate.dashboard'));

        $dashboard_response->assertOk()
            ->assertSee('href="' . route('affiliate.guide.index') . '"', false)
            ->assertSee(trans('affiliate.dashboard.open_guide', [], app()->getLocale()));
    }

    #[Test]
    public function affiliate_finance_sidebar_uses_the_english_guide_label(): void
    {
        $user = $this->CreateAffiliate(AffiliateProfileStatus::ACTIVE);
        $user->update(['language_code' => 'th']);

        $this->actingAs($user);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();

        $this->assertStringContainsString('<p>Affiliate Guide</p>', $menu);
    }

    private function CreateAffiliate(AffiliateProfileStatus $status): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-GUIDE-' . $user->id,
            'status' => $status,
        ]);

        return $user->fresh();
    }
}
