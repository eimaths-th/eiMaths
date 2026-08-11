<?php

namespace App\Models;

use App\Models\User;
use App\Models\Scopes\LegacyAwareSoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับจัดการสื่อการตลาด Affiliate (แยกออกจาก PromotionImage)
 *
 * @property integer $id
 * @property string $title
 * @property string $file_path
 * @property string $type
 * @property string $description
 * @property string $status
 * @property integer $created_by
 * @property string $created_at
 * @property string $updated_at
 */
class AffiliateContent extends Model
{
    use SoftDeletes;

    protected static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new LegacyAwareSoftDeletingScope());

        static::deleting(function (self $content): void {
            if ($content->isForceDeleting() || ! Schema::hasColumn($content->getTable(), $content->getDeletedAtColumn())) {
                return;
            }

            $content->setAttribute($content->getDeletedAtColumn(), $content->freshTimestamp());
            $content->saveQuietly();
        });
    }
    /**
     * ชื่อตารางในฐานข้อมูล
     *
     * @var string
     */
    protected $table = 'affiliate_contents';

    /**
     * คอลัมน์ที่อนุญาตให้ mass assign ได้
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'file_path',
        'type',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'publish_starts_at' => 'immutable_datetime',
        'publish_ends_at' => 'immutable_datetime',
        'archived_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'archived_by' => 'integer',
    ];

    public function scopePublishedAt($query, ?\DateTimeInterface $at = null)
    {
        $moment = $at ?? now();
        return $query->where('status', 'enabled')
            ->whereNull('archived_at')
            ->where(function ($builder) use ($moment): void {
                $builder->whereNull('publish_starts_at')->orWhere('publish_starts_at', '<=', $moment);
            })
            ->where(function ($builder) use ($moment): void {
                $builder->whereNull('publish_ends_at')->orWhere('publish_ends_at', '>', $moment);
            });
    }

    /**
     * Relationship: ผู้สร้างสื่อ (Admin)
     */
    public function Creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function Updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function Archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
