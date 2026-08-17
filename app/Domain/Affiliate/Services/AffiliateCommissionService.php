<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateCommissionSourceItem;
use App\Domain\Affiliate\Models\AffiliateCommissionReversal;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\ValueObjects\Money;
use App\Models\Receipt;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AffiliateCommissionService
{
    public function __construct(
        private readonly AffiliateCommissionCalculator $calculator,
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateWalletService $wallet_service,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function Calculate(\App\Models\Order $order): \App\Domain\Affiliate\ValueObjects\CommissionCalculation
    {
        $order->loadMissing('orderDetailLists');
        return $this->calculator->Calculate($order);
    }

    public function CreateForConfirmedPayment(
        Receipt $receipt,
        DateTimeInterface $confirmed_at,
        string $correlation_id,
    ): ?AffiliateCommission {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (!$this->feature_gate->IsEnabled('commission_creation')) {
            return null;
        }

        return DB::transaction(function () use ($receipt, $confirmed_at): ?AffiliateCommission {
            $locked_receipt = Receipt::query()->whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            $order = \App\Models\Order::query()->whereKey($locked_receipt->oid)->lockForUpdate()->first();
            if ($order === null || !$this->IsConfirmed($locked_receipt, $order) || $order->affiliate_attribution_id === null) {
                return null;
            }

            $existing = AffiliateCommission::query()->where('receipt_id', $locked_receipt->id)->first();
            if ($existing !== null) {
                return $existing;
            }

            $attribution = \App\Domain\Affiliate\Models\AffiliateAttribution::query()
                ->whereKey($order->affiliate_attribution_id)
                ->where('contact_id', $order->cid)
                ->first();
            if ($attribution === null) {
                return null;
            }

            $order->load('orderDetailLists');
            $calculation = $this->calculator->Calculate($order);
            $source_key = 'affiliate:commission:receipt:' . $locked_receipt->id;
            $release_at = CarbonImmutable::instance($confirmed_at)->addDays($this->configuration_service->CommissionHoldingDays());
            $commission = AffiliateCommission::create([
                'affiliate_profile_id' => $attribution->affiliate_profile_id,
                'affiliate_attribution_id' => $attribution->id,
                'contact_id' => $order->cid,
                'order_id' => $order->id,
                'receipt_id' => $locked_receipt->id,
                'eligible_tuition_minor' => $calculation->eligible_tuition_minor,
                'rate_basis_points' => $calculation->rate_basis_points,
                'commission_minor' => $calculation->commission_minor,
                'reversed_minor' => 0,
                'status' => AffiliateCommissionStatus::PENDING,
                'release_at' => $release_at,
                'source_key' => $source_key,
            ]);

            foreach ($order->orderDetailLists as $detail) {
                AffiliateCommissionSourceItem::create([
                    'affiliate_commission_id' => $commission->id,
                    'order_detail_list_id' => $detail->id,
                    'tuition_minor' => Money::FromDecimal((string) ($detail->price ?? '0'), true)->Minor(),
                ]);
            }

            return $commission->fresh(['SourceItems']);
        });
    }

    public function ReleaseMatured(int $batch = 100, ?DateTimeInterface $now = null): int
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (!$this->feature_gate->IsEnabled('commission_release') || $batch < 1) {
            return 0;
        }

        $released = 0;
        $moment = CarbonImmutable::instance($now ?? now());
        $commissions = AffiliateCommission::query()
            ->where('status', AffiliateCommissionStatus::PENDING)
            ->where('release_at', '<=', $moment)
            ->orderBy('id')
            ->limit(min($batch, 1000))
            ->get();

        foreach ($commissions as $commission) {
            $processed = DB::transaction(function () use ($commission, $moment): bool {
                $locked = AffiliateCommission::query()->whereKey($commission->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== AffiliateCommissionStatus::PENDING || $locked->release_at->greaterThan($moment)) {
                    return false;
                }

                $remaining = $locked->commission_minor - $locked->reversed_minor;
                if ($remaining <= 0) {
                    $locked->forceFill(['status' => AffiliateCommissionStatus::REVERSED])->save();
                    return true;
                }

                $this->wallet_service->ApplyEntry(
                    $locked->AffiliateProfile()->firstOrFail(),
                    \App\Domain\Affiliate\Enums\AffiliateLedgerEntryType::COMMISSION_RELEASE,
                    $remaining,
                    0,
                    'affiliate_commission',
                    $locked->id,
                    'affiliate:commission-release:' . $locked->id,
                    null,
                    'COMMISSION_RELEASE',
                    'affiliate-release-' . $locked->id,
                );
                $locked->forceFill(['status' => AffiliateCommissionStatus::AVAILABLE])->save();
                return true;
            });
            $released += $processed ? 1 : 0;
        }

        return $released;
    }

    public function Hold(AffiliateCommission $commission, User $actor, string $reason, string $correlation_id): AffiliateCommission
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (trim($reason) === '') {
            throw AffiliateDomainException::StateConflict('A commission hold reason is required.');
        }

        return DB::transaction(function () use ($commission, $actor, $reason, $correlation_id): AffiliateCommission {
            $locked = AffiliateCommission::query()->whereKey($commission->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === AffiliateCommissionStatus::HELD) {
                return $locked;
            }
            if ($locked->status !== AffiliateCommissionStatus::PENDING) {
                throw AffiliateDomainException::StateConflict('Only pending commissions can be held.');
            }
            $locked->forceFill([
                'status' => AffiliateCommissionStatus::HELD,
                'held_by' => $actor->id,
                'held_at' => now(),
                'hold_reason' => trim($reason),
            ])->save();
            $this->audit_service->Record('commission.hold', 'affiliate_commission', $locked->id, $actor, ['status' => AffiliateCommissionStatus::PENDING->value], ['status' => AffiliateCommissionStatus::HELD->value, 'reason' => trim($reason)], null, 'SUCCESS', null, $correlation_id);
            return $locked->fresh();
        });
    }

    public function ReleaseHold(AffiliateCommission $commission, User $actor, string $correlation_id): AffiliateCommission
    {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        return DB::transaction(function () use ($commission, $actor, $correlation_id): AffiliateCommission {
            $locked = AffiliateCommission::query()->whereKey($commission->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== AffiliateCommissionStatus::HELD) {
                throw AffiliateDomainException::StateConflict('Only held commissions can be released.');
            }
            $locked->forceFill(['status' => AffiliateCommissionStatus::PENDING, 'held_by' => null, 'held_at' => null, 'hold_reason' => null])->save();
            $this->audit_service->Record('commission.hold.release', 'affiliate_commission', $locked->id, $actor, ['status' => AffiliateCommissionStatus::HELD->value], ['status' => AffiliateCommissionStatus::PENDING->value], null, 'SUCCESS', null, $correlation_id);
            return $locked->fresh();
        });
    }

    public function RecordConfirmedExternalRefund(
        Receipt $receipt,
        string $refunded_tuition_amount,
        string $external_refund_reference,
        string $reason,
        string $idempotency_key,
        \App\Models\User $actor,
        string $correlation_id,
    ): AffiliateCommissionReversal {
        $this->configuration_service->AssertAffiliateWritesAvailable();
        if (!$this->feature_gate->IsEnabled('confirmed_refunds')) {
            throw AffiliateDomainException::ConfigurationInvalid('Confirmed refund recording is disabled.');
        }
        $refunded_minor = \App\Domain\Affiliate\ValueObjects\Money::FromDecimal($refunded_tuition_amount, true)->Minor();
        if ($refunded_minor < 1 || trim($reason) === '' || trim($external_refund_reference) === '' || trim($idempotency_key) === '') {
            throw AffiliateDomainException::StateConflict('Refund evidence is incomplete.');
        }

        return DB::transaction(function () use ($receipt, $refunded_minor, $external_refund_reference, $reason, $idempotency_key, $actor, $correlation_id): AffiliateCommissionReversal {
            $existing = AffiliateCommissionReversal::query()->where('idempotency_key', $idempotency_key)->first();
            if ($existing !== null) {
                return $existing;
            }

            if (AffiliateCommissionReversal::query()->where('external_refund_reference', $external_refund_reference)->exists()) {
                throw AffiliateDomainException::IdempotencyConflict();
            }

            $commission = AffiliateCommission::query()->where('receipt_id', $receipt->id)->lockForUpdate()->firstOrFail();
            $refunded_before = (int) $commission->Reversals()->sum('refunded_tuition_minor');
            $credit_note_reversed_before = (int) $commission->CreditNoteAdjustments()->sum('tuition_reversal_delta_minor');
            if ($refunded_before + $credit_note_reversed_before + $refunded_minor > $commission->eligible_tuition_minor) {
                throw AffiliateDomainException::StateConflict('Refunded tuition exceeds the remaining eligible tuition.');
            }

            $reversal_minor = \App\Domain\Affiliate\ValueObjects\CommissionCalculation::FromValues(
                $refunded_minor,
                0,
                $refunded_minor,
                (int) $commission->rate_basis_points,
            )->commission_minor;
            $remaining_commission = $commission->commission_minor - $commission->reversed_minor;
            $reversal_minor = min($reversal_minor, $remaining_commission);
            $reversal = AffiliateCommissionReversal::create([
                'affiliate_commission_id' => $commission->id,
                'external_refund_reference' => $external_refund_reference,
                'refunded_tuition_minor' => $refunded_minor,
                'reversal_minor' => $reversal_minor,
                'idempotency_key' => $idempotency_key,
                'recorded_by' => $actor->id,
                'reason' => trim($reason),
                'occurred_at' => now(),
            ]);

            if ($commission->status === AffiliateCommissionStatus::AVAILABLE && $reversal_minor > 0) {
                $this->wallet_service->ApplyEntry(
                    $commission->AffiliateProfile()->firstOrFail(),
                    AffiliateLedgerEntryType::COMMISSION_REVERSAL,
                    -$reversal_minor,
                    0,
                    'affiliate_commission_reversal',
                    $reversal->id,
                    'affiliate:commission-reversal:' . $reversal->id,
                    $actor,
                    'COMMISSION_REVERSAL',
                    $correlation_id,
                );
            }

            $new_reversed_minor = $commission->reversed_minor + $reversal_minor;
            $commission->forceFill([
                'reversed_minor' => $new_reversed_minor,
                'status' => $new_reversed_minor >= $commission->commission_minor ? AffiliateCommissionStatus::REVERSED : $commission->status,
            ])->save();
            $this->audit_service->Record('commission.refund.record', 'affiliate_commission', $commission->id, $actor, [], ['reversal_id' => $reversal->id], $reversal_minor, 'SUCCESS', null, $correlation_id);

            return $reversal;
        });
    }

    private function IsConfirmed(Receipt $receipt, \App\Models\Order $order): bool
    {
        $receipt_confirmed = (int) $receipt->status === 1
            && trim((string) $receipt->receipt_number) !== ''
            && trim((string) $receipt->payment_date) !== '';
        $direct_paid = (int) $order->status === 1
            && trim((string) $receipt->receipt_number) !== ''
            && trim((string) $receipt->payment_date) !== '';

        return $receipt_confirmed || $direct_paid;
    }
}
