<?php

namespace App\Models;

use App\Models\User;
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

    /**
     * Relationship: ผู้สร้างสื่อ (Admin)
     */
    public function Creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
