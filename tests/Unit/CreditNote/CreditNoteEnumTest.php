<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\Enums\BuyerBranchType;
use App\Domain\CreditNote\Enums\CreditNoteEventOperation;
use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteEnumTest extends TestCase
{
    #[Test]
    public function LifecycleAndEvidenceEnumsRoundTripTheirStoredValues(): void
    {
        $this->assertSame('draft', CreditNoteStatus::DRAFT->value);
        $this->assertSame(CreditNoteStatus::ISSUED, CreditNoteStatus::from('issued'));
        $this->assertSame('cancelled', CreditNoteStatus::CANCELLED->value);
        $this->assertSame(CreditNoteSourceType::RECEIPT_DETAIL, CreditNoteSourceType::from('receipt_detail'));
        $this->assertSame(CreditNotePostingType::CANCELLATION, CreditNotePostingType::from('cancellation'));
        $this->assertSame(CreditNoteEventOperation::UPDATED, CreditNoteEventOperation::from('updated'));
        $this->assertSame(BuyerBranchType::HEAD_OFFICE, BuyerBranchType::from('head_office'));
        $this->assertSame(ReceiptLineType::SERVICE_CHARGE, ReceiptLineType::from('service_charge'));
        $this->assertSame(TaxTreatment::VAT_INCLUSIVE, TaxTreatment::from('vat_inclusive'));
    }

    #[Test]
    public function ReasonEnumContainsExactlyTheApprovedTenStoredValues(): void
    {
        $actual_values = array_map(
            static fn (CreditNoteReasonCode $reason_code): string => $reason_code->value,
            CreditNoteReasonCode::cases()
        );

        $this->assertSame([
            'product_price_overstated',
            'service_price_overstated',
            'product_defect_damage_or_shortage',
            'service_nonconformity_or_shortage',
            'product_return',
            'statutory_compensation',
            'advance_deposit_or_reservation_return',
            'commercial_return_or_exchange',
            'defective_service_termination',
            'service_not_provided_termination',
        ], $actual_values);
        $this->assertNull(CreditNoteReasonCode::tryFrom('other'));
        $this->assertNull(CreditNoteReasonCode::tryFrom('unknown'));
    }
}
