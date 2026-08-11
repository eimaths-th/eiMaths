<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Http\Middleware\SetAffiliateLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.default_locale', 'th');
    }

    #[Test]
    public function AffiliateDefaultsToThaiWhenPreferenceIsMissing(): void
    {
        [$user] = $this->CreateActiveAffiliate(null);

        $response = $this->actingAs($user)->get(route('affiliate.dashboard'));

        $response->assertOk()->assertSee('<html lang="th">', false);
    }

    #[Test]
    public function AuthenticatedEnglishPreferenceWins(): void
    {
        [$user] = $this->CreateActiveAffiliate('en');

        $response = $this->actingAs($user)->get(route('affiliate.dashboard'));

        $response->assertOk()->assertSee('<html lang="en">', false);
    }

    #[Test]
    public function UnsupportedPreferenceFallsBackToThai(): void
    {
        [$user] = $this->CreateActiveAffiliate('ja');

        $response = $this->actingAs($user)->get(route('affiliate.dashboard'));

        $response->assertOk()->assertSee('<html lang="th">', false);
    }

    #[Test]
    public function UnrelatedRouteKeepsGlobalEnglishLocaleAfterAffiliateRequest(): void
    {
        [$user] = $this->CreateActiveAffiliate('th');
        $this->actingAs($user);

        $request = Request::create('/unrelated', 'GET');
        $middleware = app(SetAffiliateLocale::class);
        $response = $middleware->handle($request, function (Request $request) {
            return response('<html lang="' . app()->getLocale() . '">');
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<html lang="th">', $response->getContent());
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function NewAffiliateRegistrationUsesThaiDefault(): void
    {
        $user = app(AffiliateAccountService::class)->Register([
            'name' => 'Thai Affiliate',
            'email' => 'thai-locale@example.test',
            'phone' => '0812345678',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $this->assertSame('th', $user->language_code);
    }

    private function CreateActiveAffiliate(?string $language_code): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'language_code' => $language_code,
            'password' => Hash::make('secret'),
        ]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-LOCALE-' . $user->id,
            'status' => 'ACTIVE',
        ]);

        return [$user, $profile];
    }
}
