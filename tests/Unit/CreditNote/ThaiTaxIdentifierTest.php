<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\ValueObjects\ThaiTaxIdentifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ThaiTaxIdentifierTest extends TestCase
{
    #[Test]
    public function NormalizesSeparatorsAndPreservesThirteenDigits(): void
    {
        $identifier = ThaiTaxIdentifier::FromInput('010-5561171606');

        $this->assertSame('0105561171606', $identifier->Digits());
    }

    #[Test]
    public function RejectsMalformedAndInvalidChecksumIdentifiers(): void
    {
        foreach (['010556117160', '0105561171607', '๐๑๐๕๕๖๑๑๗๑๖๐๖', '010 5561 171606x'] as $tax_id) {
            $this->expectException(\InvalidArgumentException::class);
            ThaiTaxIdentifier::FromInput($tax_id);
        }
    }
}
