<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $centre
 * @property integer $student_id
 * @property integer $level_id
 * @property string $term
 * @property string $bookuse
 * @property string $course_remaining
 * @property string $date
 * @property string $stime
 * @property string $etime
 * @property string $start_course
 * @property string $comment
 * @property string $signature
 * @property string $created_at
 * @property string $updated_at
 */
class Histrories extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['centre','student_id', 'level_id', 'term', 'bookuse', 'course_remaining', 'date', 'stime', 'etime', 'start_date', 'comment', 'signature', 'created_at', 'updated_at'];

    // public function student()
    // {
    //     return $this->hasMany(Contact::class, 'student_id','id');
    //     // return $this->hasOne(Receipt_School::class, 'pid','id');
    // }
    public function contacts() {
        return $this->belongsTo(Contact::class, 'student_id');
    }

    public function departments() {
        return $this->belongsTo(Department::class, 'centre');
    }

}
