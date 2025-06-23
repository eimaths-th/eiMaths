<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $count
 * @property string $year
 * @property string $month
 * @property string $created_at
 * @property string $updated_at
 */
class affiliateUserCourseCount extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'affiliate_user_course_count';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'count', 'year', 'month', 'created_at', 'updated_at'];
}
