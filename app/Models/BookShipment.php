<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookShipment extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_DISPATCHED = 'DISPATCHED';
    public const STATUS_CENTRE_RECEIVED = 'CENTRE_RECEIVED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const DERIVED_DRAFT = 'DRAFT';
    public const DERIVED_WAITING_FOR_CENTRE = 'WAITING_FOR_CENTRE';
    public const DERIVED_PENDING_STUDENT_DELIVERY = 'PENDING_STUDENT_DELIVERY';
    public const DERIVED_COMPLETED = 'COMPLETED';
    public const DERIVED_COMPLETED_WITH_ISSUES = 'COMPLETED_WITH_ISSUES';
    public const DERIVED_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'shipment_number',
        'destination_centre_id',
        'destination_centre_code',
        'destination_centre_name',
        'status',
        'created_by',
        'created_by_name',
        'dispatched_by',
        'dispatched_by_name',
        'dispatched_at',
        'centre_received_by',
        'centre_received_by_name',
        'centre_received_at',
        'cancelled_by',
        'cancelled_by_name',
        'cancelled_at',
        'cancellation_reason',
        'dispatch_notified_at',
        'centre_received_notified_at',
        'student_completion_notified_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'centre_received_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'dispatch_notified_at' => 'datetime',
        'centre_received_notified_at' => 'datetime',
        'student_completion_notified_at' => 'datetime',
    ];

    public function DestinationCentre(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'destination_centre_id');
    }

    public function CreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function DispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function CentreReceivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'centre_received_by');
    }

    public function Items(): HasMany
    {
        return $this->hasMany(BookShipmentItem::class, 'book_shipment_id')
            ->orderBy('id');
    }

    public function Logs(): HasMany
    {
        return $this->hasMany(BookShipmentLog::class, 'book_shipment_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
