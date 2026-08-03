<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookShipmentItem extends Model
{
    use HasFactory;

    public const CENTRE_STATUS_PENDING = 'PENDING';
    public const CENTRE_STATUS_RECEIVED = 'RECEIVED';
    public const CENTRE_STATUS_MISSING = 'MISSING';
    public const CENTRE_STATUS_DAMAGED = 'DAMAGED';

    protected $fillable = [
        'book_shipment_id',
        'source_order_detail_list_id',
        'source_student_id',
        'source_order_id',
        'replaces_shipment_item_id',
        'source_student_code',
        'source_student_name',
        'source_order_number',
        'book_name',
        'level',
        'term',
        'centre_status',
        'centre_note',
    ];

    public function BookShipment(): BelongsTo
    {
        return $this->belongsTo(BookShipment::class, 'book_shipment_id');
    }

    public function SourceBook(): BelongsTo
    {
        return $this->belongsTo(OrderDetailList::class, 'source_order_detail_list_id');
    }

    public function SourceStudent(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'source_student_id');
    }

    public function SourceOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    public function ReplacesShipmentItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_shipment_item_id');
    }

    public function ReplacementItems(): HasMany
    {
        return $this->hasMany(self::class, 'replaces_shipment_item_id');
    }
}
