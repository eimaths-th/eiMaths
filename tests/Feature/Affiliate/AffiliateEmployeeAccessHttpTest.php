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

class AffiliateEmployeeAccessHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Department::create(['id' => 1, 'name' => 'HQ test', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        \App\Models\Department::forceCreate(['id' => 5, 'name' => 'Employee branch', 'code' => 'EMP', 'status' => 1]);
        Config::set('affiliate.features.employee_enrollment', true);
        Config::set('affiliate.abuse.employee_enrollment_attempts_per_minute', 6);
    }

    #[Test]
    public function GuestIsRedirectedToLogin(): void
    {
        $this->get(route('affiliate.employee-access.show'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function EligibleEmployeeCanOpenTheAccessPage(): void
    {
        $user = $this->CreateEligibleEmployee();

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertViewIs('affiliate.employee-access')
            ->assertViewHas('user', fn (User $view_user): bool => $view_user->is($user))
            ->assertSee(__('affiliate.employee_access.menu_label'))
            ->assertSee(__('affiliate.employee_access.no_profile'))
            ->assertSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function PendingEmailEmployeeSeesEnrollmentForm(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::PENDING_EMAIL);

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee(__('affiliate.employee_access.pending_email'))
            ->assertSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function PendingApprovalEmployeeSeesNoRepeatEnrollmentForm(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::PENDING_APPROVAL);

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee(__('affiliate.employee_access.pending_approval'))
            ->assertDontSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function ActiveEmployeeSeesOnlyDashboardAction(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::ACTIVE);

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee(__('affiliate.employee_access.active'))
            ->assertSee(__('affiliate.employee_access.open_dashboard'))
            ->assertDontSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function SuspendedEmployeeSeesContactAdministratorGuidance(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::SUSPENDED);

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee(__('affiliate.employee_access.suspended'))
            ->assertSee(__('affiliate.employee_access.contact_administrator'))
            ->assertDontSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function DeactivatedEmployeeSeesContactAdministratorGuidance(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::DEACTIVATED);

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee(__('affiliate.employee_access.deactivated'))
            ->assertSee(__('affiliate.employee_access.contact_administrator'))
            ->assertDontSee(__('affiliate.employee_access.request_action'));
    }

    #[Test]
    public function SessionStatusFeedbackIsAnnounced(): void
    {
        $user = $this->CreateEligibleEmployee();

        $this->actingAs($user)
            ->withSession(['status' => __('affiliate.employee_access.enrollment_requested')])
            ->get(route('affiliate.employee-access.show'))
            ->assertOk()
            ->assertSee('role="status"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee(__('affiliate.employee_access.enrollment_requested'));

    }

    #[Test]
    public function EmployeeAffiliateMenuIsVisibleOnlyForEligibleFeatureEnabledUsers(): void
    {
        $eligible_user = $this->CreateEligibleEmployee();

        $this->actingAs($eligible_user);
        $this->assertStringContainsString(
            __('affiliate.employee_access.menu_label'),
            view('layouts.header')->render(),
        );

        Config::set('affiliate.features.employee_enrollment', false);
        $this->assertStringNotContainsString(
            __('affiliate.employee_access.menu_label'),
            view('layouts.header')->render(),
        );

        Config::set('affiliate.features.employee_enrollment', true);
        $this->actingAs($this->CreateIneligibleUser());
        $this->assertStringNotContainsString(
            __('affiliate.employee_access.menu_label'),
            view('layouts.header')->render(),
        );
    }

    #[Test]
    public function IneligibleEmployeeAccessReturnsForbiddenJson(): void
    {
        $user = $this->CreateIneligibleUser();

        $this->actingAs($user)
            ->getJson(route('affiliate.employee-access.show'))
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'AFFILIATE_EMPLOYEE_ENROLLMENT_DENIED');
    }

    #[Test]
    public function IneligibleEmployeeAccessReturnsPlainForbiddenResponse(): void
    {
        $user = $this->CreateIneligibleUser();

        $this->actingAs($user)
            ->get(route('affiliate.employee-access.show'))
            ->assertStatus(403)
            ->assertSee(__('affiliate.errors.employee_enrollment_denied'));
    }

    #[Test]
    public function EligibleEmployeeCanEnrollThroughPostRedirect(): void
    {
        $user = $this->CreateEligibleEmployee();

        $this->actingAs($user)
            ->post(route('affiliate.employee-access.store'), [], ['X-Correlation-ID' => 'corr-http-enroll'])
            ->assertRedirect(route('affiliate.employee-access.show'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('affiliate_profiles', [
            'user_id' => $user->id,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL->value,
        ]);
    }

    #[Test]
    public function PendingEmailEnrollmentUsesBypassFeedback(): void
    {
        $user = $this->CreateEligibleEmployee();
        $this->CreateProfile($user, AffiliateProfileStatus::PENDING_EMAIL);

        $this->actingAs($user)
            ->post(route('affiliate.employee-access.store'))
            ->assertRedirect(route('affiliate.employee-access.show'))
            ->assertSessionHas('status', __('affiliate.employee_access.email_bypass_requested'));
    }

    #[Test]
    public function SubmittedUserIdentifiersAreIgnored(): void
    {
        $user = $this->CreateEligibleEmployee();
        $other_user = $this->CreateEligibleEmployee();

        $this->actingAs($user)
            ->post(route('affiliate.employee-access.store'), [
                'user_id' => $other_user->id,
                'profile_id' => 999999,
            ])
            ->assertRedirect(route('affiliate.employee-access.show'));

        $this->assertDatabaseHas('affiliate_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('affiliate_profiles', ['user_id' => $other_user->id]);
    }

    #[Test]
    public function DisabledFeatureReturnsLocalizedServiceUnavailableWithoutMutation(): void
    {
        $user = $this->CreateEligibleEmployee();
        Config::set('affiliate.features.employee_enrollment', false);

        $this->actingAs($user)
            ->getJson(route('affiliate.employee-access.show'))
            ->assertStatus(503)
            ->assertJsonPath('error_code', 'AFFILIATE_CONFIGURATION_INVALID')
            ->assertJsonPath('message', __('affiliate.errors.configuration_invalid'));

        $this->assertDatabaseMissing('affiliate_profiles', ['user_id' => $user->id]);
    }

    #[Test]
    public function MaintenanceModeBlocksPostWithoutMutation(): void
    {
        $user = $this->CreateEligibleEmployee();
        Config::set('affiliate.maintenance_mode', true);

        $this->actingAs($user)
            ->postJson(route('affiliate.employee-access.store'))
            ->assertStatus(503)
            ->assertJsonPath('error_code', 'AFFILIATE_CONFIGURATION_INVALID');

        $this->assertDatabaseMissing('affiliate_profiles', ['user_id' => $user->id]);
    }

    #[Test]
    public function SeventhEnrollmentRequestReceivesLocalizedRateLimitResponse(): void
    {
        $user = $this->CreateEligibleEmployee();

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->actingAs($user)
                ->postJson(route('affiliate.employee-access.store'))
                ->assertStatus(302);
        }

        $limited = $this->actingAs($user)
            ->postJson(route('affiliate.employee-access.store'));

        $limited->assertStatus(429)
            ->assertSee(__('affiliate.employee_access.rate_limited'))
            ->assertHeader('X-RateLimit-Limit', '6')
            ->assertHeader('X-RateLimit-Remaining', '0');
    }

    private function CreateEligibleEmployee(): User
    {
        $user = User::factory()->create([
            'department_id' => 5,
            'password' => Hash::make('employee-password'),
            'email_verified_at' => null,
        ]);
        $user->assignRole(Role::findOrCreate('Teacher', 'web'));

        return $user->fresh();
    }

    private function CreateIneligibleUser(): User
    {
        $user = User::factory()->create([
            'department_id' => 5,
            'password' => Hash::make('user-password'),
            'email_verified_at' => null,
        ]);
        $user->assignRole(Role::findOrCreate('User', 'web'));

        return $user->fresh();
    }

    private function CreateProfile(User $user, AffiliateProfileStatus $status): AffiliateProfile
    {
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-HTTP-' . $user->id . '-' . $status->value,
            'status' => $status,
        ]);
    }
}
