<?php

namespace App\Domain\CreditNote\Models;

use App\Domain\CreditNote\Enums\BuyerBranchType;
use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $fillable = [
        'receipt_id', 'centre_id', 'status', 'number', 'currency', 'reason_code', 'reason_details',
        'adjustment_event_date', 'late_issue_reason', 'issue_date', 'issued_at', 'issue_idempotency_key',
        'cancellation_effective_date', 'cancelled_at', 'cancellation_reason', 'cancel_idempotency_key',
        'source_snapshot_hash', 'source_receipt_updated_at', 'original_receipt_number', 'original_receipt_date',
        'original_billing_details_text', 'buyer_name', 'buyer_address', 'buyer_tax_id', 'buyer_is_vat_registered',
        'buyer_branch_type', 'buyer_branch_code', 'issuer_name', 'issuer_address', 'issuer_tax_id',
        'issuer_branch_type', 'issuer_branch_code', 'original_taxable_base_minor', 'original_vat_minor',
        'original_exempt_minor', 'original_out_of_scope_minor', 'original_grand_total_minor',
        'corrected_taxable_base_minor', 'corrected_vat_minor', 'corrected_exempt_minor',
        'corrected_out_of_scope_minor', 'corrected_grand_total_minor', 'difference_taxable_base_minor',
        'difference_vat_minor', 'difference_exempt_minor', 'difference_out_of_scope_minor',
        'difference_grand_total_minor', 'created_by', 'updated_by', 'issued_by', 'cancelled_by', 'lock_version',
        'active_draft_receipt_id', 'issue_payload_hash', 'cancel_payload_hash',
    ];

    protected $casts = [
        'status' => CreditNoteStatus::class,
        'reason_code' => CreditNoteReasonCode::class,
        'buyer_branch_type' => BuyerBranchType::class,
        'adjustment_event_date' => 'date:Y-m-d',
        'issue_date' => 'date:Y-m-d',
        'original_receipt_date' => 'date:Y-m-d',
        'cancellation_effective_date' => 'date:Y-m-d',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'source_receipt_updated_at' => 'datetime',
        'buyer_is_vat_registered' => 'boolean',
        'lock_version' => 'integer',
        'original_taxable_base_minor' => 'integer',
        'original_vat_minor' => 'integer',
        'original_exempt_minor' => 'integer',
        'original_out_of_scope_minor' => 'integer',
        'original_grand_total_minor' => 'integer',
        'corrected_taxable_base_minor' => 'integer',
        'corrected_vat_minor' => 'integer',
        'corrected_exempt_minor' => 'integer',
        'corrected_out_of_scope_minor' => 'integer',
        'corrected_grand_total_minor' => 'integer',
        'difference_taxable_base_minor' => 'integer',
        'difference_vat_minor' => 'integer',
        'difference_exempt_minor' => 'integer',
        'difference_out_of_scope_minor' => 'integer',
        'difference_grand_total_minor' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (): void {
            throw new \LogicException('Credit Note documents are append-only and cannot be deleted.');
        });

        static::updating(function (CreditNote $credit_note): void {
            $original_status = CreditNoteStatus::tryFrom((string) $credit_note->getRawOriginal('status'));
            if ($original_status === null || $original_status === CreditNoteStatus::DRAFT) {
                return;
            }

            if ($original_status === CreditNoteStatus::CANCELLED) {
                throw new \LogicException('Cancelled Credit Notes are immutable.');
            }

            if ($credit_note->status !== CreditNoteStatus::CANCELLED) {
                throw new \LogicException('Issued Credit Notes may only transition to Cancelled.');
            }

            $allowed_attributes = [
                'status', 'cancellation_effective_date', 'cancelled_at', 'cancellation_reason',
                'cancel_idempotency_key', 'cancelled_by', 'cancel_payload_hash', 'updated_at',
            ];
            $disallowed_attributes = array_diff(array_keys($credit_note->getDirty()), $allowed_attributes);
            if ($disallowed_attributes !== []) {
                throw new \LogicException('Issued Credit Note fields are immutable.');
            }
        });
    }

    public function Receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function Department()
    {
        return $this->belongsTo(Department::class, 'centre_id');
    }

    public function CreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function IssuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function CancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function Items()
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id')->orderBy('sort_order')->orderBy('id');
    }

    public function Postings()
    {
        return $this->hasMany(CreditNotePosting::class, 'credit_note_id')->orderBy('id');
    }

    public function AffiliateAdjustments()
    {
        return $this->hasMany(\App\Domain\Affiliate\Models\AffiliateCommissionCreditNoteAdjustment::class, 'credit_note_id');
    }
}
