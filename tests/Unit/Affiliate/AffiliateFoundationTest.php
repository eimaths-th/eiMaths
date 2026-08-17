<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\ValueObjects\CommissionCalculation;
use App\Domain\Affiliate\ValueObjects\Money;
use Tests\TestCase;

class AffiliateFoundationTest extends TestCase
{
    public function test_money_parses_exact_minor_units_without_float_arithmetic(): void
    {
        $this->assertSame(0, Money::FromDecimal('0')->Minor());
        $this->assertSame(12345, Money::FromDecimal('123.45')->Minor());
        $this->assertSame(-125, Money::FromDecimal('-1.25')->Minor());
        $this->assertSame('123.45', Money::FromMinor(12345)->Decimal());
        $this->assertSame('-1.25', Money::FromMinor(-125)->Decimal());
    }

    public function test_money_rejects_invalid_precision_and_overflow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::FromDecimal('1.001');
    }

    public function test_money_rejects_integer_overflow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::FromDecimal('92233720368547758.08');
    }

    public function test_money_can_require_non_negative_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::FromDecimal('-0.01', true);
    }

    public function test_commission_calculation_uses_half_up_basis_point_arithmetic(): void
    {
        $calculation = CommissionCalculation::FromValues(
            gross_tuition_minor: 10001,
            tuition_discount_minor: 0,
            eligible_tuition_minor: 10001,
            rate_basis_points: 5000,
        );

        $this->assertSame(5001, $calculation->commission_minor);
    }

    public function test_commission_calculation_is_immutable_and_exposes_approved_fields(): void
    {
        $calculation = CommissionCalculation::FromValues(10000, 1000, 9000, 2000);

        $this->assertSame(10000, $calculation->gross_tuition_minor);
        $this->assertSame(1000, $calculation->tuition_discount_minor);
        $this->assertSame(9000, $calculation->eligible_tuition_minor);
        $this->assertSame(2000, $calculation->rate_basis_points);
        $this->assertSame(1800, $calculation->commission_minor);
    }

    public function test_enums_match_approved_lifecycle_values(): void
    {
        $this->assertSame([
            'PENDING_EMAIL',
            'PENDING_APPROVAL',
            'ACTIVE',
            'SUSPENDED',
            'DEACTIVATED',
        ], array_column(AffiliateProfileStatus::cases(), 'value'));
        $this->assertSame(['PROMPTPAY', 'BANK'], array_column(AffiliatePayoutType::cases(), 'value'));
        $this->assertSame(['PENDING', 'HELD', 'AVAILABLE', 'REVERSED'], array_column(AffiliateCommissionStatus::cases(), 'value'));
        $this->assertSame([
            'OPENING_BALANCE',
            'COMMISSION_RELEASE',
            'COMMISSION_REVERSAL',
            'WITHDRAWAL_RESERVE',
            'WITHDRAWAL_RELEASE',
            'WITHDRAWAL_PAID',
            'ADJUSTMENT',
            'CREDIT_NOTE_COMMISSION_REVERSAL',
            'CREDIT_NOTE_COMMISSION_RESTORATION',
        ], array_column(AffiliateLedgerEntryType::cases(), 'value'));
        $this->assertSame(['REQUESTED', 'APPROVED', 'PAID', 'REJECTED', 'CANCELLED'], array_column(AffiliateWithdrawalStatus::cases(), 'value'));
        $this->assertSame(['OPEN', 'RESOLVED'], array_column(AffiliateReconciliationStatus::cases(), 'value'));
    }

    public function test_domain_exception_exposes_safe_mapping(): void
    {
        $exception = AffiliateDomainException::FinancialSourceLocked();

        $this->assertSame('Financial source is locked.', $exception->Reason());
        $this->assertSame('AFFILIATE_FINANCIAL_SOURCE_LOCKED', $exception->ErrorCode());
        $this->assertSame(409, $exception->StatusCode());
        $this->assertSame('affiliate.errors.financial_source_locked', $exception->TranslationKey());
    }

    public function test_feature_flags_are_disabled_by_default(): void
    {
        foreach ([
            'referral_capture',
            'commission_creation',
            'commission_release',
            'confirmed_refunds',
            'wallet_withdrawals',
            'administration',
        ] as $feature_name) {
            $this->assertFalse((bool) config('affiliate.features.' . $feature_name));
        }
    }
}
