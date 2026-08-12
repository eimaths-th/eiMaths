<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TUITION_PREFIX = 'ค่าคอร์สเรียน ';
    private const BOOK_DESCRIPTION = 'ค่าหนังสือ';
    private const DEPOSIT_DESCRIPTION = 'ค่ามัดจำ';
    private const REGISTRATION_DESCRIPTION = 'ค่าลงทะเบียนแรกเข้า';
    private const EQUIPMENT_DESCRIPTION = 'ค่าอุปกรณ์การเรียน';

    public function up(): void
    {
        Schema::table('receipt_details', function (Blueprint $table): void {
            $table->string('line_type', 40)->nullable()->default('unknown');
            $table->string('tax_treatment', 40)->nullable();
            $table->unsignedInteger('vat_rate_basis_points')->nullable();
        });

        DB::table('receipt_details')
            ->orderBy('id')
            ->chunkById(500, function ($receipt_details): void {
                foreach ($receipt_details as $receipt_detail) {
                    $description = (string) ($receipt_detail->des ?? '');
                    $line_type = $this->ResolveLineType($description);
                    [$tax_treatment, $vat_rate_basis_points] = $this->ResolveTaxMetadata($receipt_detail->tax);

                    DB::table('receipt_details')
                        ->where('id', $receipt_detail->id)
                        ->update([
                            'line_type' => $line_type,
                            'tax_treatment' => $tax_treatment,
                            'vat_rate_basis_points' => $vat_rate_basis_points,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('receipt_details', function (Blueprint $table): void {
            $table->dropColumn(['line_type', 'tax_treatment', 'vat_rate_basis_points']);
        });
    }

    private function ResolveLineType(string $description): string
    {
        if (str_starts_with($description, self::TUITION_PREFIX)) {
            return 'tuition';
        }

        return match ($description) {
            self::BOOK_DESCRIPTION => 'book',
            self::DEPOSIT_DESCRIPTION => 'deposit',
            self::REGISTRATION_DESCRIPTION => 'registration_fee',
            self::EQUIPMENT_DESCRIPTION => 'equipment',
            default => 'unknown',
        };
    }

    private function ResolveTaxMetadata(mixed $legacy_tax): array
    {
        return match ((int) $legacy_tax) {
            1 => ['vat_inclusive', 700],
            0 => ['vat_exempt', 0],
            default => [null, null],
        };
    }
};
