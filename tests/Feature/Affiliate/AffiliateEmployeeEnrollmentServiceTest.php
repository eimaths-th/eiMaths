<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateEmployeeEnrollmentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateEmployeeEnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.employee_enrollment', true);
        Notification::fake();
    }

    #[Test]
    public function EligibleEmployeeCreatesPendingApprovalWithoutEmailVerification(): void
    {
        $user = $this->CreateEligibleEmployee();
        $before = $this->SnapshotUser($user);

        $profile = app(AffiliateEmployeeEnrollmentService::class)->Enroll($user, 'corr-enroll-create');

        $this->assertSame(AffiliateProfileStatus::PENDING_APPROVAL, $profile->status);
        $this->assertSame($profile->referral_code, $user->fresh()->referral);
        $this->AssertUserUnchanged($before, $user->fresh());
        $this->assertSame(
            1,
            AffiliateAuditLog::query()
                ->where('operation', 'affiliate.employee-enrollment.created')
                ->where('subject_id', $profile->id)
                ->count()
        );
        Notification::assertNothingSent();
    }

    #[Test]
    public function PendingEmailEmployeeIsNormalizedWithoutEmailMutation(): void
    {
        $user = $this->CreateEligibleEmployee(['email_verified_at' => null]);
        $profile = $this->CreateProfile($user, AffiliateProfileStatus::PENDING_EMAIL);
        $before = $this->SnapshotUser($user);

        $result = app(AffiliateEmployeeEnrollmentService::class)->Enroll($user, 'corr-enroll-pending-email');

        $this->assertSame($profile->id, $result->id);
        $this->assertSame(AffiliateProfileStatus::PENDING_APPROVAL, $result->status);
        $this->AssertUserUnchanged($before, $user->fresh());
        $this->assertSame(
            1,
            AffiliateAuditLog::query()
                ->where('operation', 'affiliate.employee-enrollment.email-bypassed')
                ->where('subject_id', $profile->id)
                ->count()
        );
        Notification::assertNothingSent();
    }

    #[Test]
    public function RepeatedEnrollmentReturnsOneProfileAndOneCreationAudit(): void
    {
        $user = $this->CreateEligibleEmployee();
        $service = app(AffiliateEmployeeEnrollmentService::class);

        $first = $service->Enroll($user, 'corr-enroll-repeat-1');
        $second = $service->Enroll($user->fresh(), 'corr-enroll-repeat-2');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, AffiliateProfile::query()->where('user_id', $user->id)->count());
        $this->assertSame(
            1,
            AffiliateAuditLog::query()
                ->where('operation', 'affiliate.employee-enrollment.created')
                ->where('subject_id', $first->id)
                ->count()
        );
        Notification::assertNothingSent();
    }

    #[Test]
    public function ExistingLifecycleStateIsNeverReset(): void
    {
        $service = app(AffiliateEmployeeEnrollmentService::class);

        foreach ([
            AffiliateProfileStatus::PENDING_APPROVAL,
            AffiliateProfileStatus::ACTIVE,
            AffiliateProfileStatus::SUSPENDED,
            AffiliateProfileStatus::DEACTIVATED,
        ] as $status) {
            $user = $this->CreateEligibleEmployee();
            $profile = $this->CreateProfile($user, $status);

            $result = $service->Enroll($user, 'corr-enroll-state-' . $status->value);

            $this->assertSame($profile->id, $result->id);
            $this->assertSame($status, $result->status);
            $this->assertSame(
                0,
                AffiliateAuditLog::query()->where('subject_id', $profile->id)->count()
            );
        }

        Notification::assertNothingSent();
    }

    #[Test]
    public function ExistingLegacyReferralIsNotOverwritten(): void
    {
        $user = $this->CreateEligibleEmployee(['referral' => 'LEGACY-REFERRAL']);

        $profile = app(AffiliateEmployeeEnrollmentService::class)->Enroll($user, 'corr-enroll-legacy');

        $this->assertSame('LEGACY-REFERRAL', $user->fresh()->referral);
        $this->assertNotSame('LEGACY-REFERRAL', $profile->referral_code);
        Notification::assertNothingSent();
    }

    #[Test]
    public function IneligibleUserCannotEnroll(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('employee-password'),
            'email_verified_at' => null,
        ]);
        $user->assignRole(Role::findOrCreate('User', 'web'));

        try {
            app(AffiliateEmployeeEnrollmentService::class)->Enroll($user, 'corr-enroll-denied');
            $this->fail('Expected an ineligible user to be rejected.');
        } catch (AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_EMPLOYEE_ENROLLMENT_DENIED', $exception->ErrorCode());
            $this->assertSame(403, $exception->StatusCode());
        }

        $this->assertSame(0, AffiliateProfile::query()->where('user_id', $user->id)->count());
        Notification::assertNothingSent();
    }

    #[Test]
    public function DisabledOrMaintenanceModeCannotMutate(): void
    {
        $disabled_user = $this->CreateEligibleEmployee();
        Config::set('affiliate.features.employee_enrollment', false);

        try {
            app(AffiliateEmployeeEnrollmentService::class)->Enroll($disabled_user, 'corr-enroll-disabled');
            $this->fail('Expected the disabled feature to reject enrollment.');
        } catch (AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_CONFIGURATION_INVALID', $exception->ErrorCode());
            $this->assertSame(503, $exception->StatusCode());
        }

        Config::set('affiliate.features.employee_enrollment', true);
        Config::set('affiliate.maintenance_mode', true);
        $maintenance_user = $this->CreateEligibleEmployee();

        try {
            app(AffiliateEmployeeEnrollmentService::class)->Enroll($maintenance_user, 'corr-enroll-maintenance');
            $this->fail('Expected maintenance mode to reject enrollment.');
        } catch (AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_CONFIGURATION_INVALID', $exception->ErrorCode());
            $this->assertSame(503, $exception->StatusCode());
        }

        $this->assertSame(0, AffiliateProfile::query()->whereIn('user_id', [$disabled_user->id, $maintenance_user->id])->count());
        $this->assertSame(0, AffiliateAuditLog::query()->whereIn('correlation_id', ['corr-enroll-disabled', 'corr-enroll-maintenance'])->count());
        Notification::assertNothingSent();
    }

    private function CreateEligibleEmployee(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('employee-password'),
            'email_verified_at' => null,
            'phone' => '0812345678',
            'department_id' => 5,
            'position_id' => 13,
            'affiliate_request' => 0,
            'disable' => 0,
            'referral' => null,
        ], $attributes));
        $role = Role::findOrCreate('Teacher', 'web');
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('affiliate-user', 'web'));
        $user->assignRole($role);

        return $user->fresh();
    }

    private function CreateProfile(User $user, AffiliateProfileStatus $status): AffiliateProfile
    {
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ENROLL-' . $user->id . '-' . $status->value,
            'status' => $status,
        ]);
    }

    private function SnapshotUser(User $user): array
    {
        return [
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'password' => $user->password,
            'phone' => $user->phone,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'affiliate_request' => $user->affiliate_request,
            'disable' => $user->disable,
            'roles' => $user->getRoleNames()->sort()->values()->all(),
        ];
    }

    private function AssertUserUnchanged(array $before, User $after): void
    {
        $this->assertSame($before, $this->SnapshotUser($after));
    }
}
