<?php

namespace App\Models;

use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class receipt_details extends Model
{
    use HasFactory;

    protected $casts = [
        'line_type' => ReceiptLineType::class,
        'tax_treatment' => TaxTreatment::class,
        'vat_rate_basis_points' => 'integer',
    ];
}
