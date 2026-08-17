<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\ValueObjects\ReceiptLineMetadata;

final class ReceiptLineMetadataResolver
{
    private const TUITION_PREFIX = 'ค่าคอร์สเรียน ';
    private const BOOK_DESCRIPTION = 'ค่าหนังสือ';
    private const DEPOSIT_DESCRIPTION = 'ค่ามัดจำ';
    private const REGISTRATION_DESCRIPTION = 'ค่าลงทะเบียนแรกเข้า';
    private const EQUIPMENT_DESCRIPTION = 'ค่าอุปกรณ์การเรียน';

    public function ResolveGenerated(ReceiptLineType $line_type, int $legacy_tax): ReceiptLineMetadata
    {
        return new ReceiptLineMetadata($line_type, ...$this->ResolveTax($legacy_tax));
    }

    public function ResolveEdited(string $description, int $legacy_tax, ?string $submitted_line_type): ReceiptLineMetadata
    {
        $line_type = $submitted_line_type === null || trim($submitted_line_type) === ''
            ? $this->ResolveDescription($description)
            : ReceiptLineType::tryFrom($submitted_line_type);

        if (!$line_type instanceof ReceiptLineType) {
            throw new \InvalidArgumentException('Receipt line type is invalid.');
        }

        return new ReceiptLineMetadata($line_type, ...$this->ResolveTax($legacy_tax));
    }

    private function ResolveDescription(string $description): ReceiptLineType
    {
        if (str_starts_with($description, self::TUITION_PREFIX)) {
            return ReceiptLineType::TUITION;
        }

        return match ($description) {
            self::BOOK_DESCRIPTION => ReceiptLineType::BOOK,
            self::DEPOSIT_DESCRIPTION => ReceiptLineType::DEPOSIT,
            self::REGISTRATION_DESCRIPTION => ReceiptLineType::REGISTRATION_FEE,
            self::EQUIPMENT_DESCRIPTION => ReceiptLineType::EQUIPMENT,
            default => ReceiptLineType::UNKNOWN,
        };
    }

    private function ResolveTax(int $legacy_tax): array
    {
        return match ($legacy_tax) {
            1 => [TaxTreatment::VAT_INCLUSIVE, 700],
            0 => [TaxTreatment::VAT_EXEMPT, 0],
            default => throw new \InvalidArgumentException('Receipt tax flag is invalid.'),
        };
    }
}
