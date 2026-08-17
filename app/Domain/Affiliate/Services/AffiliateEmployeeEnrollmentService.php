<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class AffiliateEmployeeEnrollmentService
{
    private const FEATURE_NAME = 'employee_enrollment';
    private const OPERATION_CREATED = 'affiliate.employee-enrollment.created';
    private const OPERATION_EMAIL_BYPASSED = 'affiliate.employee-enrollment.email-bypassed';
    private const SUBJECT_TYPE = 'affiliate_profile';

    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateEmployeeEligibility $eligibility,
        private readonly AffiliateReferralCodeGenerator $referral_code_generator,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function Enroll(User $user, string $correlation_id): AffiliateProfile
    {
        $this->feature_gate->AssertEnabled(self::FEATURE_NAME);
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (!$this->eligibility->IsEligible($user)) {
            throw AffiliateDomainException::EmployeeEnrollmentDenied();
        }

        try {
            return DB::transaction(function () use ($user, $correlation_id): AffiliateProfile {
                $locked_user = User::query()
                    ->whereKey($user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $profile = AffiliateProfile::query()
                    ->where('user_id', $locked_user->id)
                    ->lockForUpdate()
                    ->first();

                if ($profile !== null) {
                    return $this->NormalizeExistingProfile($profile, $locked_user, $correlation_id);
                }

                return $this->CreateProfile($locked_user, $correlation_id);
            });
        } catch (QueryException $exception) {
            if (!$this->IsAffiliateProfileOwnershipConflict($exception)) {
                throw $exception;
            }

            $winning_profile = AffiliateProfile::query()
                ->where('user_id', $user->getKey())
                ->first();
            if ($winning_profile === null) {
                throw $exception;
            }

            return $winning_profile;
        }
    }

    private function NormalizeExistingProfile(
        AffiliateProfile $profile,
        User $actor,
        string $correlation_id,
    ): AffiliateProfile {
        if ($profile->status !== AffiliateProfileStatus::PENDING_EMAIL) {
            return $profile->fresh();
        }

        $profile->forceFill([
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ])->save();
        $this->audit_service->Record(
            self::OPERATION_EMAIL_BYPASSED,
            self::SUBJECT_TYPE,
            $profile->id,
            $actor,
            ['status' => AffiliateProfileStatus::PENDING_EMAIL->value],
            ['status' => AffiliateProfileStatus::PENDING_APPROVAL->value],
            null,
            'SUCCESS',
            null,
            $correlation_id,
        );

        return $profile->fresh();
    }

    private function CreateProfile(User $user, string $correlation_id): AffiliateProfile
    {
        $referral_code = $this->referral_code_generator->Generate();
        if (trim((string) $user->referral) === '') {
            $user->forceFill(['referral' => $referral_code])->save();
        }

        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_code,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);
        $this->audit_service->Record(
            self::OPERATION_CREATED,
            self::SUBJECT_TYPE,
            $profile->id,
            $user,
            [],
            ['status' => AffiliateProfileStatus::PENDING_APPROVAL->value],
            null,
            'SUCCESS',
            null,
            $correlation_id,
        );

        return $profile->fresh();
    }

    private function IsAffiliateProfileOwnershipConflict(QueryException $exception): bool
    {
        $sql_state = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        if (!in_array($sql_state, ['23000', '23505'], true)) {
            return false;
        }

        $message = strtolower($exception->getMessage());
        return str_contains($message, 'affiliate_profiles_user_id_unique')
            || str_contains($message, 'affiliate_profiles.user_id');
    }
}
