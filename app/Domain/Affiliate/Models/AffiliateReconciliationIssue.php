<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReconciliationIssue extends Model
{
    protected $table = 'affiliate_reconciliation_issues';

    protected $fillable = [
        'source_table',
        'source_id',
        'issue_code',
        'severity',
        'evidence',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'adjustment_ledger_entry_id',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'evidence' => 'array',
        'status' => AffiliateReconciliationStatus::class,
        'resolved_by' => 'integer',
        'resolved_at' => 'immutable_datetime',
        'adjustment_ledger_entry_id' => 'integer',
    ];

    public function ResolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function AdjustmentLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(AffiliateLedgerEntry::class, 'adjustment_ledger_entry_id');
    }
}
