<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $img //path file
 * @property string $title
 * @property string $status
 * @property string $url
 * @property string $created_by
 * @property string $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PromotionImage extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'promotion_img';

    /**
     * @var array
     */
    protected $fillable = ['img', 'title', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
