<?php

namespace App\Domain\Affiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateWalletBalance extends Model
{
    protected $table = 'affiliate_wallet_balances';

    protected $primaryKey = 'affiliate_profile_id';

    public $incrementing = false;

    protected $fillable = [
        'affiliate_profile_id',
        'available_balance_minor',
        'reserved_balance_minor',
        'last_ledger_entry_id',
    ];

    protected $casts = [
        'affiliate_profile_id' => 'integer',
        'available_balance_minor' => 'integer',
        'reserved_balance_minor' => 'integer',
        'last_ledger_entry_id' => 'integer',
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function LastLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(AffiliateLedgerEntry::class, 'last_ledger_entry_id');
    }
}
