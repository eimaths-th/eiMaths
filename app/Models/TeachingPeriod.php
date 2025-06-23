<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $department_id
 * @property string $day
 * @property string $period
 * @property string $created_at
 * @property string $updated_at
 */
class TeachingPeriod extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'teaching_period';

    /**
     * @var array
     */
    protected $fillable = ['department_id', 'day', 'period', 'created_at', 'updated_at'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
