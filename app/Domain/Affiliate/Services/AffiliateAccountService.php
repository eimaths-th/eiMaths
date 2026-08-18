<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Notifications\AffiliateApprovedNotification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AffiliateAccountService
{
    public function __construct(
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateAuditService $audit_service,
        private readonly AffiliateReferralCodeGenerator $referral_code_generator,
        private readonly AffiliateEmployeeEligibility $employee_eligibility,
    ) {
    }

    public function Register(array $validated): User
    {
        $this->configuration_service->AssertAffiliateRegistrationDeliveryAvailable();
        $user = null;
        DB::transaction(function () use ($validated, &$user): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'],
                'affiliate_request' => 1,
                'language_code' => (string) config('affiliate.default_locale', 'th'),
                'department_id' => 5,
                'position_id' => 13,
                'referral' => $this->referral_code_generator->Generate(),
                'password' => Hash::make($validated['password']),
            ]);

            AffiliateProfile::create([
                'user_id' => $user->id,
                'referral_code' => $user->referral,
                'status' => AffiliateProfileStatus::PENDING_EMAIL,
            ]);

            if (\Spatie\Permission\Models\Role::where('name', 'Affiliate-user')->where('guard_name', 'web')->exists()) {
                $user->assignRole('Affiliate-user');
            }

            $this->audit_service->Record('affiliate.register', 'affiliate_profile', $user->AffiliateProfile()->value('id'), null, [], ['status' => AffiliateProfileStatus::PENDING_EMAIL->value], null, 'SUCCESS', null, (string) Str::uuid());

            DB::afterCommit(fn () => event(new Registered($user)));
        });

        return $user;
    }

    public function MarkEmailVerified(User $user): AffiliateProfile
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        return DB::transaction(function () use ($user): AffiliateProfile {
            $profile = AffiliateProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            if ($profile->status === AffiliateProfileStatus::PENDING_EMAIL) {
                $profile->forceFill(['status' => AffiliateProfileStatus::PENDING_APPROVAL])->save();
                $this->audit_service->Record('affiliate.email-verified', 'affiliate_profile', $profile->id, $user, ['status' => AffiliateProfileStatus::PENDING_EMAIL->value], ['status' => AffiliateProfileStatus::PENDING_APPROVAL->value], null, 'SUCCESS', null, (string) Str::uuid());
            }

            return $profile->fresh();
        });
    }

    public function Approve(AffiliateProfile $profile, User $actor, ?string $reason = null): AffiliateProfile
    {
        $approved_profile = $this->Transition($profile, $actor, AffiliateProfileStatus::PENDING_APPROVAL, AffiliateProfileStatus::ACTIVE, 'approved_by', 'approved_at', $reason);
        $this->NotifyExternalAffiliateApproval($approved_profile);

        return $approved_profile;
    }

    public function Suspend(AffiliateProfile $profile, User $actor, string $reason): AffiliateProfile
    {
        return $this->Transition($profile, $actor, AffiliateProfileStatus::ACTIVE, AffiliateProfileStatus::SUSPENDED, 'suspended_by', 'suspended_at', $reason);
    }

    public function Reactivate(AffiliateProfile $profile, User $actor, ?string $reason = null): AffiliateProfile
    {
        return $this->Transition($profile, $actor, AffiliateProfileStatus::SUSPENDED, AffiliateProfileStatus::ACTIVE, null, null, $reason);
    }

    public function Deactivate(AffiliateProfile $profile, User $actor, string $reason): AffiliateProfile
    {
        return $this->Transition($profile, $actor, [AffiliateProfileStatus::ACTIVE, AffiliateProfileStatus::SUSPENDED], AffiliateProfileStatus::DEACTIVATED, 'deactivated_by', 'deactivated_at', $reason);
    }

    private function Transition(
        AffiliateProfile $profile,
        User $actor,
        AffiliateProfileStatus|array $from,
        AffiliateProfileStatus $to,
        ?string $actor_field,
        ?string $timestamp_field,
        ?string $reason,
    ): AffiliateProfile {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if ($reason === '' || ($to === AffiliateProfileStatus::DEACTIVATED && $reason === null)) {
            throw AffiliateDomainException::StateConflict('A lifecycle reason is required.');
        }

        return DB::transaction(function () use ($profile, $actor, $from, $to, $actor_field, $timestamp_field, $reason): AffiliateProfile {
            $locked = AffiliateProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            $allowed = is_array($from) ? in_array($locked->status, $from, true) : $locked->status === $from;
            if (!$allowed) {
                throw AffiliateDomainException::StateConflict('Affiliate lifecycle transition is not allowed.');
            }

            $previous_status = $locked->status->value;
            $attributes = ['status' => $to, 'status_reason' => $reason];
            if ($actor_field !== null) {
                $attributes[$actor_field] = $actor->id;
            }
            if ($timestamp_field !== null) {
                $attributes[$timestamp_field] = now();
            }
            $locked->forceFill($attributes)->save();
            $this->audit_service->Record('affiliate.lifecycle', 'affiliate_profile', $locked->id, $actor, ['status' => $previous_status], ['status' => $to->value, 'reason' => $reason], null, 'SUCCESS', null, (string) Str::uuid());

            return $locked->fresh();
        });
    }

    private function NotifyExternalAffiliateApproval(AffiliateProfile $profile): void
    {
        $user = $profile->User;
        if (!$user instanceof User || $this->employee_eligibility->IsEligible($user)) {
            return;
        }

        if (trim((string) $user->email) === '') {
            Log::warning('affiliate.approval-notification.skipped', [
                'affiliate_profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'reason' => 'missing_email',
            ]);

            return;
        }

        try {
            $user->notify(new AffiliateApprovedNotification((string) $profile->referral_code));
        } catch (Throwable $exception) {
            Log::error('affiliate.approval-notification.failed', [
                'affiliate_profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'exception' => $exception,
            ]);
        }
    }

}
