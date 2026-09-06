<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateIdentityStatus;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateIdentityProfile;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AffiliateIdentityService
{
    public function __construct(
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateAuditService $audit_service,
    ) {}

    public function Submit(User $user, array $validated, ?UploadedFile $document, string $correlation_id): AffiliateIdentityProfile
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        $new_path = null;
        $old_path = null;

        if ($document instanceof UploadedFile) {
            $extension = strtolower($document->extension() ?: 'jpg');
            $new_path = $document->storeAs(
                'affiliate-identity/' . $user->getKey(),
                Str::uuid() . '.' . $extension,
                'local',
            );
        }

        try {
            $identity = DB::transaction(function () use ($user, $validated, $document, $new_path, &$old_path, $correlation_id): AffiliateIdentityProfile {
                $profile = AffiliateProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
                if ($profile->status !== AffiliateProfileStatus::PENDING_IDENTITY) {
                    throw AffiliateDomainException::StateConflict('Identity submission is not available for this account.');
                }

                $identity = AffiliateIdentityProfile::query()
                    ->where('affiliate_profile_id', $profile->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if (!in_array($identity->status, [AffiliateIdentityStatus::DRAFT, AffiliateIdentityStatus::REJECTED], true)) {
                    throw AffiliateDomainException::StateConflict('Identity submission is already under review.');
                }

                $previous_identity_status = $identity->status->value;
                $old_path = $identity->id_document_path;
                $attributes = [
                    'honorific' => $validated['honorific'],
                    'legal_first_name' => trim($validated['legal_first_name']),
                    'legal_last_name' => trim($validated['legal_last_name']),
                    'national_id' => $validated['national_id'],
                    'national_id_hash' => AffiliateIdentityProfile::SensitiveHash($validated['national_id']),
                    'card_expires_on' => $validated['card_expires_on'],
                    'address_line1' => trim($validated['address_line1']),
                    'subdistrict' => trim($validated['subdistrict']),
                    'district' => trim($validated['district']),
                    'province' => trim($validated['province']),
                    'postal_code' => $validated['postal_code'],
                    'status' => AffiliateIdentityStatus::PENDING,
                    'submitted_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'rejection_reason' => null,
                ];
                if ($document instanceof UploadedFile && is_string($new_path)) {
                    $attributes += [
                        'id_document_path' => $new_path,
                        'id_document_mime' => $document->getMimeType(),
                        'id_document_size' => $document->getSize(),
                    ];
                }

                $identity->forceFill($attributes)->save();
                $profile->forceFill(['status' => AffiliateProfileStatus::PENDING_APPROVAL, 'status_reason' => null])->save();
                $this->audit_service->Record(
                    'affiliate.identity-submitted',
                    'affiliate_identity_profile',
                    $identity->id,
                    $user,
                    ['status' => $previous_identity_status],
                    ['status' => AffiliateIdentityStatus::PENDING->value, 'profile_status' => AffiliateProfileStatus::PENDING_APPROVAL->value],
                    null,
                    'SUCCESS',
                    null,
                    $correlation_id,
                );

                return $identity->fresh();
            });
        } catch (Throwable $exception) {
            if (is_string($new_path)) {
                Storage::disk('local')->delete($new_path);
            }
            throw $exception;
        }

        if (is_string($new_path) && is_string($old_path) && $old_path !== $new_path) {
            Storage::disk('local')->delete($old_path);
        }

        return $identity;
    }

    public function Reject(AffiliateProfile $profile, User $actor, string $reason, string $correlation_id): AffiliateIdentityProfile
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();

        return DB::transaction(function () use ($profile, $actor, $reason, $correlation_id): AffiliateIdentityProfile {
            $locked_profile = AffiliateProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            $identity = AffiliateIdentityProfile::query()->where('affiliate_profile_id', $locked_profile->id)->lockForUpdate()->firstOrFail();
            if ($locked_profile->status !== AffiliateProfileStatus::PENDING_APPROVAL || $identity->status !== AffiliateIdentityStatus::PENDING) {
                throw AffiliateDomainException::StateConflict('Identity review is not pending.');
            }

            $identity->forceFill([
                'status' => AffiliateIdentityStatus::REJECTED,
                'reviewed_at' => now(),
                'reviewed_by' => $actor->id,
                'rejection_reason' => $reason,
            ])->save();
            $locked_profile->forceFill([
                'status' => AffiliateProfileStatus::PENDING_IDENTITY,
                'status_reason' => $reason,
            ])->save();
            $this->audit_service->Record(
                'affiliate.identity-rejected',
                'affiliate_identity_profile',
                $identity->id,
                $actor,
                ['status' => AffiliateIdentityStatus::PENDING->value],
                ['status' => AffiliateIdentityStatus::REJECTED->value, 'reason' => $reason],
                null,
                'SUCCESS',
                null,
                $correlation_id,
            );

            return $identity->fresh();
        });
    }
}
