<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookShipmentLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    public const ACTION_CREATED = 'CREATED';
    public const ACTION_ITEMS_UPDATED = 'ITEMS_UPDATED';
    public const ACTION_DISPATCHED = 'DISPATCHED';
    public const ACTION_CANCELLED = 'CANCELLED';
    public const ACTION_DRAFT_DISCARDED = 'DRAFT_DISCARDED';
    public const ACTION_CENTRE_RECEIVED = 'CENTRE_RECEIVED';

    protected $fillable = [
        'book_shipment_id',
        'acted_by',
        'action',
        'actor_name',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function BookShipment(): BelongsTo
    {
        return $this->belongsTo(BookShipment::class, 'book_shipment_id');
    }

    public function ActedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
