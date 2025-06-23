<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $course_per_month
 * @property float $comission_per_course_10_percent
 * @property float $comission_per_course_15_percent
 * @property integer $user_per_course_low
 * @property integer $user_per_course_high
 * @property string $created_at
 * @property string $updated_at
 */
class affiliateConfigCommission extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'affiliate_config_commission';

    /**
     * @var array
     */
    protected $fillable = ['course_per_month', 'comission_per_course_10_percent', 'comission_per_course_15_percent', 'user_per_course_low', 'user_per_course_high', 'created_at', 'updated_at'];
}
