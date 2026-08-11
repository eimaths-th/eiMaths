<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_pending_profile_without_logging_user_in(): void
    {
        Notification::fake();
        $user = app(AffiliateAccountService::class)->Register([
            'name' => 'New Affiliate',
            'email' => 'new-affiliate@example.test',
            'phone' => '0812345678',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $this->assertFalse(auth()->check());
        $this->assertSame(1, $user->affiliate_request);
        $this->assertSame(AffiliateProfileStatus::PENDING_EMAIL, $user->fresh()->AffiliateProfile->status);
        $this->assertNotSame('', (string) $user->fresh()->AffiliateProfile->referral_code);
    }

    public function test_production_registration_fails_closed_when_mail_driver_is_log(): void
    {
        Config::set('app.env', 'production');
        Config::set('mail.default', 'log');

        try {
            app(AffiliateAccountService::class)->Register([
                'name' => 'Blocked Affiliate',
                'email' => 'blocked-affiliate@example.test',
                'phone' => '0812345678',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
            ]);
            $this->fail('Expected production registration to require a real mail driver.');
        } catch (AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_CONFIGURATION_INVALID', $exception->ErrorCode());
            $this->assertSame(503, $exception->StatusCode());
        }

        $this->assertDatabaseMissing('users', ['email' => 'blocked-affiliate@example.test']);
    }

    public function test_email_verification_moves_only_pending_email_profile_to_pending_approval(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret'), 'email_verified_at' => null]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-VERIFY-' . $user->id,
            'status' => AffiliateProfileStatus::PENDING_EMAIL,
        ]);

        app(AffiliateAccountService::class)->MarkEmailVerified($user);

        $this->assertSame(AffiliateProfileStatus::PENDING_APPROVAL, $profile->fresh()->status);
    }

    public function test_affiliate_registration_is_rate_limited_by_client_ip(): void
    {
        Log::spy();
        $client_ip = '198.51.100.42';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $client_ip])
                ->post(route('affiliate.register'), [])
                ->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $client_ip])
            ->post(route('affiliate.register'), [])
            ->assertStatus(429);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.43'])
            ->post(route('affiliate.register'), [])
            ->assertStatus(302);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context) use ($client_ip): bool {
                return $message === 'affiliate.registration.rate_limited'
                    && ($context['ip'] ?? null) === $client_ip;
            });
    }

    public function test_waiting_page_renders_for_guest(): void
    {
        $response = $this->get(route('affiliate.waiting'));

        $response->assertOk()
            ->assertSee('Thanks For Registration')
            ->assertSee('Please Wait For Approval')
            ->assertDontSee('frontend.Thanks For Registration')
            ->assertDontSee('frontend.Please Wait For Approval');
    }

    #[Test]
    public function PendingAffiliateCannotAccessGeneralHome(): void
    {
        $affiliate_department = Department::create([
            'name' => 'Affiliate',
            'code' => 'AFF',
            'status' => 1,
        ]);

        $affiliate_user = User::factory()->create([
            'password' => Hash::make('affiliate-password'),
            'department_id' => $affiliate_department->id,
        ]);
        $affiliate_user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        AffiliateProfile::create([
            'user_id' => $affiliate_user->id,
            'referral_code' => 'AFF-HOME-GUARD',
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);

        $response = $this->actingAs($affiliate_user)->get('/home');

        $response->assertRedirect(route('affiliate.waiting'));
    }

    #[Test]
    public function ActiveAffiliateIsRedirectedFromWaitingPageToDashboard(): void
    {
        $affiliate_user = User::factory()->create([
            'password' => Hash::make('active-affiliate-password'),
        ]);
        $affiliate_user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        AffiliateProfile::create([
            'user_id' => $affiliate_user->id,
            'referral_code' => 'AFF-ACTIVE-WAITING',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        $response = $this->actingAs($affiliate_user)->get(route('affiliate.waiting'));

        $response->assertRedirect(route('affiliate.dashboard'));
    }

    #[Test]
    public function PendingAffiliateCanViewWaitingPage(): void
    {
        $affiliate_user = User::factory()->create([
            'password' => Hash::make('pending-affiliate-password'),
        ]);
        $affiliate_user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        AffiliateProfile::create([
            'user_id' => $affiliate_user->id,
            'referral_code' => 'AFF-PENDING-WAITING',
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);

        $response = $this->actingAs($affiliate_user)->get(route('affiliate.waiting'));

        $response->assertOk()
            ->assertSee('Please Wait For Approval');
    }
}
