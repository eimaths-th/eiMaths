<?php

namespace App\Domain\Affiliate\Models;

use App\Models\OrderDetailList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateCommissionSourceItem extends Model
{
    protected $table = 'affiliate_commission_source_items';

    protected $fillable = ['affiliate_commission_id', 'order_detail_list_id', 'tuition_minor'];

    protected $casts = ['tuition_minor' => 'integer'];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Affiliate commission source items are immutable evidence.');
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate commission source items are append-only evidence.');
        });
    }

    public function AffiliateCommission(): BelongsTo
    {
        return $this->belongsTo(AffiliateCommission::class, 'affiliate_commission_id');
    }

    public function OrderDetailList(): BelongsTo
    {
        return $this->belongsTo(OrderDetailList::class, 'order_detail_list_id');
    }
}
