<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\Affiliate\Models\AffiliateCommissionSourceItem;

class OrderDetailList extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'level', 'term', 'book', 'price', 'bprice', 'bqty', 'received_at', 'received_by'];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function receiptLogs(): HasMany
    {
        return $this->hasMany(BookReceiptLog::class, 'order_detail_list_id');
    }

    public function ActiveBookShipmentItem(): BelongsTo
    {
        return $this->belongsTo(BookShipmentItem::class, 'active_book_shipment_item_id');
    }

    public function AffiliateCommissionSourceItems(): HasMany
    {
        return $this->hasMany(AffiliateCommissionSourceItem::class, 'order_detail_list_id');
    }
}
