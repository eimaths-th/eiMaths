<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\Services\ReceiptLineMetadataResolver;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReceiptController;
use App\Models\receipt_details;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceiptLineMetadataWriteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function GeneratedLineTypesAndLegacyTaxFlagsProduceStructuredMetadata(): void
    {
        $resolver = app(ReceiptLineMetadataResolver::class);

        foreach ([
            ReceiptLineType::TUITION,
            ReceiptLineType::BOOK,
            ReceiptLineType::DEPOSIT,
            ReceiptLineType::REGISTRATION_FEE,
            ReceiptLineType::EQUIPMENT,
        ] as $line_type) {
            $metadata = $resolver->ResolveGenerated($line_type, 1);
            $this->assertSame($line_type, $metadata->line_type);
            $this->assertSame(TaxTreatment::VAT_INCLUSIVE, $metadata->tax_treatment);
            $this->assertSame(700, $metadata->vat_rate_basis_points);
        }

        $other_metadata = $resolver->ResolveGenerated(ReceiptLineType::OTHER, 0);
        $this->assertSame(ReceiptLineType::OTHER, $other_metadata->line_type);
        $this->assertSame(TaxTreatment::VAT_EXEMPT, $other_metadata->tax_treatment);
        $this->assertSame(0, $other_metadata->vat_rate_basis_points);
    }

    #[Test]
    public function EditedLinesUseExplicitTypeOrExactDescriptionFallback(): void
    {
        $resolver = app(ReceiptLineMetadataResolver::class);

        $explicit = $resolver->ResolveEdited('User description', 1, 'other');
        $this->assertSame(ReceiptLineType::OTHER, $explicit->line_type);

        $omitted = $resolver->ResolveEdited('ค่าหนังสือ', 0, null);
        $this->assertSame(ReceiptLineType::BOOK, $omitted->line_type);
        $this->assertSame(TaxTreatment::VAT_EXEMPT, $omitted->tax_treatment);

        $unknown = $resolver->ResolveEdited('ค่าคอร์สเรียนX', 1, null);
        $this->assertSame(ReceiptLineType::UNKNOWN, $unknown->line_type);
    }

    #[Test]
    public function InvalidSubmittedTypeAndInvalidLegacyTaxAreRejected(): void
    {
        $resolver = app(ReceiptLineMetadataResolver::class);

        try {
            $resolver->ResolveEdited('User description', 1, 'not-a-line-type');
            $this->fail('Invalid submitted line types must be rejected.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(\InvalidArgumentException::class);
        $resolver->ResolveGenerated(ReceiptLineType::OTHER, 2);
    }

    #[Test]
    public function ReceiptDetailCastsPersistedMetadataToDomainEnums(): void
    {
        $receipt_id = DB::table('receipts')->insertGetId(['total_fee' => '10.00']);
        $detail = new receipt_details();
        $detail->receipt_id = $receipt_id;
        $detail->des = 'ค่าหนังสือ';
        $detail->tax = 0;
        $detail->line_type = 'book';
        $detail->tax_treatment = 'vat_exempt';
        $detail->vat_rate_basis_points = 0;
        $detail->save();

        $stored = receipt_details::findOrFail($detail->id);
        $this->assertSame(ReceiptLineType::BOOK, $stored->line_type);
        $this->assertSame(TaxTreatment::VAT_EXEMPT, $stored->tax_treatment);
        $this->assertSame(0, $stored->vat_rate_basis_points);
    }

    #[Test]
    public function ExistingReceiptAndOrderControllersResolveTheMetadataDependency(): void
    {
        $this->assertInstanceOf(ReceiptController::class, app(ReceiptController::class));
        $this->assertInstanceOf(OrderController::class, app(OrderController::class));
    }
}
