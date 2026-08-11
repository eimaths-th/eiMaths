<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Models\AffiliateClickDailyMetric;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateVisitorWindow;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\ValueObjects\AffiliateClickContext;
use App\Domain\Affiliate\ValueObjects\AffiliateTrackingTarget;
use App\Models\AffiliateContent;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AffiliateClickTrackingService
{
    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateDestinationValidator $destination_validator,
        private readonly AffiliateVisitorHasher $visitor_hasher,
        private readonly AffiliateTrafficClassifier $traffic_classifier,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function ResolveDirect(string $referral_code, string $destination_target): ?AffiliateTrackingTarget
    {
        $profile = $this->ActiveProfile($referral_code);
        if ($profile === null) {
            return null;
        }

        return new AffiliateTrackingTarget(
            affiliate_profile: $profile,
            affiliate_content: null,
            campaign_code: null,
            source_type: 'direct',
            destination_target: $this->destination_validator->Normalize($destination_target),
        );
    }

    public function ResolveMedia(string $referral_code, string $campaign_code): ?AffiliateTrackingTarget
    {
        $profile = $this->ActiveProfile($referral_code);
        if ($profile === null) {
            return null;
        }

        $now_local = CarbonImmutable::now('UTC')->setTimezone((string) config('app.timezone', 'Asia/Bangkok'));
        $content = AffiliateContent::query()
            ->where('campaign_code', $campaign_code)
            ->where('status', 'enabled')
            ->whereNull('archived_at')
            ->where(function ($query) use ($now_local): void {
                $query->whereNull('publish_starts_at')->orWhere('publish_starts_at', '<=', $now_local);
            })
            ->where(function ($query) use ($now_local): void {
                $query->whereNull('publish_ends_at')->orWhere('publish_ends_at', '>', $now_local);
            })
            ->first();
        if ($content === null) {
            return null;
        }

        return new AffiliateTrackingTarget(
            affiliate_profile: $profile,
            affiliate_content: $content,
            campaign_code: $content->campaign_code,
            source_type: 'media',
            destination_target: $this->destination_validator->Normalize((string) $content->destination_target),
        );
    }

    public function Record(Request $request, AffiliateTrackingTarget $target, string $correlation_id): ?AffiliateClickContext
    {
        if (! $this->feature_gate->IsEnabled('click_tracking')) {
            return null;
        }

        $rejection_reason = $this->traffic_classifier->RejectionReason($request, $target->affiliate_profile);
        if ($rejection_reason !== null) {
            $this->audit_service->EmitSignal('click.rejected', $correlation_id, [
                'reason_code' => $rejection_reason,
                'affiliate_profile_id' => $target->affiliate_profile->id,
                'campaign_code' => $target->campaign_code,
                'source_type' => $target->source_type,
            ]);
            return null;
        }

        $visitor_hash = $this->visitor_hasher->HashRequest($request);
        $now = CarbonImmutable::now('UTC');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $context = DB::transaction(function () use ($target, $visitor_hash, $now): AffiliateClickContext {
                    $profile_id = $target->affiliate_profile->id;
                    $window_inserted = DB::table('affiliate_visitor_windows')->insertOrIgnore([
                        'affiliate_profile_id' => $profile_id,
                        'visitor_hash' => $visitor_hash,
                        'unique_window_key' => (string) Str::ulid(),
                        'last_unique_at' => $now->format('Y-m-d H:i:s'),
                        'last_seen_at' => $now->format('Y-m-d H:i:s'),
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                    $window = AffiliateVisitorWindow::query()
                        ->where('affiliate_profile_id', $profile_id)
                        ->where('visitor_hash', $visitor_hash)
                        ->lockForUpdate()
                        ->first();
                    if ($window === null) {
                        throw new \RuntimeException('Affiliate visitor window could not be locked.');
                    }

                    $opens_new_window = $window_inserted === 1 || $now->greaterThanOrEqualTo($window->last_unique_at->addDay());
                    $unique_window_key = $opens_new_window ? (string) Str::ulid() : $window->unique_window_key;
                    $is_unique = $opens_new_window;
                    $event = AffiliateClickEvent::create([
                        'affiliate_profile_id' => $profile_id,
                        'affiliate_content_id' => $target->affiliate_content?->id,
                        'campaign_code' => $target->campaign_code,
                        'source_type' => $target->source_type,
                        'destination_target' => $target->destination_target,
                        'visitor_hash' => $visitor_hash,
                        'unique_window_key' => $unique_window_key,
                        'is_unique' => $is_unique,
                        'clicked_at' => $now,
                    ]);

                    $metric_date = $now->setTimezone('Asia/Bangkok')->format('Y-m-d');
                    $dimension_key = $target->source_type === 'direct' ? 'direct' : 'media:' . $target->campaign_code;
                    DB::table('affiliate_click_daily_metrics')->insertOrIgnore([
                        'affiliate_profile_id' => $profile_id,
                        'metric_date' => $metric_date,
                        'dimension_key' => $dimension_key,
                        'affiliate_content_id' => $target->affiliate_content?->id,
                        'campaign_code' => $target->campaign_code,
                        'total_clicks' => 0,
                        'unique_clicks' => 0,
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                    $metric = AffiliateClickDailyMetric::query()
                        ->where('affiliate_profile_id', $profile_id)
                        ->where('metric_date', $metric_date)
                        ->where('dimension_key', $dimension_key)
                        ->lockForUpdate()
                        ->first();
                    if ($metric === null) {
                        throw new \RuntimeException('Affiliate click metric could not be locked.');
                    }
                    $metric->forceFill([
                        'total_clicks' => $metric->total_clicks + 1,
                        'unique_clicks' => $metric->unique_clicks + ($is_unique ? 1 : 0),
                    ])->save();

                    $window->forceFill([
                        'unique_window_key' => $unique_window_key,
                        'last_unique_at' => $is_unique ? $now : $window->last_unique_at,
                        'last_seen_at' => $now,
                    ])->save();

                    return new AffiliateClickContext(
                        click_event_id: $event->id,
                        content_id: $target->affiliate_content?->id,
                        campaign_code: $target->campaign_code,
                        source_type: $target->source_type,
                        unique_window_key: $unique_window_key,
                        destination_target: $target->destination_target,
                        is_unique: $is_unique,
                    );
                });
                $this->audit_service->EmitSignal('click.accepted', $correlation_id, [
                    'affiliate_profile_id' => $target->affiliate_profile->id,
                    'click_event_id' => $context->click_event_id,
                    'campaign_code' => $target->campaign_code,
                    'source_type' => $target->source_type,
                    'is_unique' => $context->is_unique,
                ]);

                return $context;
            } catch (QueryException $exception) {
                if ($attempt === 2) {
                    throw $exception;
                }
            }
        }

        return null;
    }

    private function ActiveProfile(string $referral_code): ?AffiliateProfile
    {
        return AffiliateProfile::query()
            ->where('referral_code', $referral_code)
            ->where('status', AffiliateProfileStatus::ACTIVE)
            ->first();
    }
}
