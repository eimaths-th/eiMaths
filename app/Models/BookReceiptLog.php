<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReceiptLog extends Model
{
    use HasFactory;

    public const ACTION_RECEIVED = 'RECEIVED';
    public const ACTION_CANCELLED = 'CANCELLED';

    public const UPDATED_AT = null;

    protected $fillable = [
        'order_detail_list_id',
        'student_id',
        'acted_by',
        'action',
        'order_number',
        'book_name',
        'student_code',
        'student_name',
        'actor_name',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(OrderDetailList::class, 'order_detail_list_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'student_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
