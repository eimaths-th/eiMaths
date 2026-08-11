<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\ValueObjects\AffiliateReferralCookie;
use App\Domain\Affiliate\ValueObjects\AffiliateClickContext;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Models\Contact;
use App\Models\CoursePending;
use App\Models\Order;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AffiliateAttributionService
{
    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function CaptureReferral(Request $request): ?AffiliateReferralCookie
    {
        if (!$this->feature_gate->IsEnabled('referral_capture')) {
            return null;
        }

        $existing = $this->ReadCookie($request->cookie(config('affiliate.cookie_name')));
        $now = CarbonImmutable::now('UTC');
        if ($existing !== null && $existing->IsValidAt($now) && $this->IsActiveProfile($existing->affiliate_profile_id)) {
            return $existing;
        }

        $referral_code = trim((string) $request->query('ref', ''));
        if ($referral_code === '') {
            return null;
        }

        $profile = AffiliateProfile::query()
            ->where('referral_code', $referral_code)
            ->where('status', AffiliateProfileStatus::ACTIVE)
            ->first();
        if ($profile === null) {
            return null;
        }

        return $this->CaptureResolvedReferral($request, $profile);
    }

    public function CaptureResolvedReferral(
        Request $request,
        AffiliateProfile $profile,
        ?AffiliateClickContext $click_context = null,
    ): ?AffiliateReferralCookie {
        if (!$this->feature_gate->IsEnabled('referral_capture') || !$this->IsActiveProfile($profile->id)) {
            return null;
        }

        $now = CarbonImmutable::now('UTC');
        $existing = $this->ReadCookie($request->cookie(config('affiliate.cookie_name')));
        if ($existing !== null && $existing->IsValidAt($now) && $this->IsActiveProfile($existing->affiliate_profile_id)) {
            return $existing;
        }

        $context = new AffiliateReferralCookie(
            $profile->id,
            $now,
            $now->addDays($this->configuration_service->AttributionDurationDays()),
            click_event_id: $click_context?->click_event_id,
            content_id: $click_context?->content_id,
            campaign_code: $click_context?->campaign_code,
            source_type: $click_context?->source_type,
            unique_window_key: $click_context?->unique_window_key,
            destination_target: $click_context?->destination_target,
        );
        $this->QueueCookie($request, $context);

        return $context;
    }

    private function QueueCookie(Request $request, AffiliateReferralCookie $context): void
    {
        Cookie::queue(cookie()->make(
            config('affiliate.cookie_name'),
            json_encode($context->ToArray(), JSON_THROW_ON_ERROR),
            $this->configuration_service->AttributionDurationDays() * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    public function ReadCookie(?string $raw_cookie): ?AffiliateReferralCookie
    {
        if ($raw_cookie === null || $raw_cookie === '') {
            return null;
        }

        try {
            $payload = json_decode($raw_cookie, true, 8, JSON_THROW_ON_ERROR);
            return is_array($payload) ? AffiliateReferralCookie::FromArray($payload) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function BindToContact(Contact $contact, ?AffiliateReferralCookie $context, string $source_context = 'student_identified'): ?AffiliateAttribution
    {
        return DB::transaction(function () use ($contact, $context, $source_context): ?AffiliateAttribution {
            $locked_contact = Contact::query()->whereKey($contact->id)->lockForUpdate()->firstOrFail();
            $now = CarbonImmutable::now('UTC');
            $current = $locked_contact->CurrentAffiliateAttribution()->with('AffiliateProfile')->first();
            if ($current !== null && $current->expires_at->greaterThan($now)) {
                return $current;
            }
            if ($context === null || !$context->IsValidAt($now) || !$this->IsActiveProfile($context->affiliate_profile_id)) {
                return null;
            }

            $attribution = new AffiliateAttribution([
                'affiliate_profile_id' => $context->affiliate_profile_id,
                'contact_id' => $locked_contact->id,
                'captured_at' => $context->captured_at,
                'expires_at' => $context->expires_at,
                'source_context' => $source_context,
            ]);
            $attribution->forceFill($this->ContextAttributes($context))->save();
            $locked_contact->forceFill(['current_affiliate_attribution_id' => $attribution->id])->save();

            return $attribution->fresh(['AffiliateProfile', 'Contact']);
        });
    }

    public function BindPendingToContact(
        CoursePending $pending,
        Contact $contact,
        string $source_context = 'course_pending',
    ): ?AffiliateAttribution {
        if (
            $pending->affiliate_profile_id === null
            || $pending->affiliate_captured_at === null
            || $pending->affiliate_expires_at === null
        ) {
            return null;
        }

        try {
            $context = new AffiliateReferralCookie(
                (int) $pending->affiliate_profile_id,
                CarbonImmutable::instance($pending->affiliate_captured_at),
                CarbonImmutable::instance($pending->affiliate_expires_at),
                click_event_id: $pending->affiliate_click_event_id,
                content_id: $pending->affiliate_content_id,
                campaign_code: $pending->affiliate_campaign_code,
                source_type: $pending->affiliate_source_type,
                unique_window_key: $pending->affiliate_unique_window_key,
                destination_target: $pending->affiliate_captured_destination,
            );
        } catch (\Throwable) {
            return null;
        }

        return $this->BindToContact($contact, $context, $source_context);
    }

    public function SnapshotOrder(Contact $contact, Order $order, ?DateTimeInterface $created_at = null): ?AffiliateAttribution
    {
        $order_created_at = CarbonImmutable::instance($created_at ?? $order->created_at ?? now());
        $attribution = $contact->fresh(['CurrentAffiliateAttribution.AffiliateProfile'])?->CurrentAffiliateAttribution;
        if ($attribution === null || !$attribution->captured_at->lessThanOrEqualTo($order_created_at) || !$order_created_at->lessThan($attribution->expires_at)) {
            return null;
        }
        if (!$this->IsActiveProfile($attribution->affiliate_profile_id)) {
            return null;
        }

        $order->forceFill([
            'affiliate_attribution_id' => $attribution->id,
            'affiliate_commission_rate_basis_points' => $this->configuration_service->CommissionRateBasisPoints(),
        ])->save();

        return $attribution;
    }

    public function PersistPendingBridge(CoursePending $pending, ?AffiliateReferralCookie $context): CoursePending
    {
        if ($context === null || !$context->IsValidAt(CarbonImmutable::now('UTC')) || !$this->IsActiveProfile($context->affiliate_profile_id)) {
            return $pending;
        }

        $pending->forceFill([
            'affiliate_profile_id' => $context->affiliate_profile_id,
            'affiliate_captured_at' => $context->captured_at,
            'affiliate_expires_at' => $context->expires_at,
            'affiliate_click_event_id' => $context->click_event_id,
            'affiliate_content_id' => $context->content_id,
            'affiliate_campaign_code' => $context->campaign_code,
            'affiliate_source_type' => $context->source_type,
            'affiliate_unique_window_key' => $context->unique_window_key,
            'affiliate_captured_destination' => $context->destination_target,
        ])->save();

        return $pending->fresh();
    }

    private function IsActiveProfile(int $profile_id): bool
    {
        return AffiliateProfile::query()
            ->whereKey($profile_id)
            ->where('status', AffiliateProfileStatus::ACTIVE)
            ->exists();
    }

    private function ContextAttributes(AffiliateReferralCookie $context): array
    {
        return [
            'affiliate_click_event_id' => $context->click_event_id,
            'affiliate_content_id' => $context->content_id,
            'affiliate_campaign_code' => $context->campaign_code,
            'affiliate_source_type' => $context->source_type,
            'affiliate_unique_window_key' => $context->unique_window_key,
            'affiliate_captured_destination' => $context->destination_target,
        ];
    }
}
