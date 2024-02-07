<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $centre
 * @property integer $student_id
 * @property integer $level_id
 * @property string $level_name
 * @property string $term
 * @property string $bookuse
 * @property string $course_remaining
 * @property string $date
 * @property string $stime
 * @property string $etime
 * @property string $start_date
 * @property string $end_date
 * @property string $comment
 * @property string $signature
 * @property string $created_at
 * @property string $updated_at
 */
class Histrories extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'histrories';

    /**
     * @var array
     */
    protected $fillable = ['centre', 'student_id', 'teacher_id', 'level_id', 'level_name', 'term', 'bookuse', 'course_remaining', 'date', 'stime', 'etime', 'start_date', 'end_date', 'comment', 'signature', 'created_at', 'updated_at'];

    public function student()
    {
        return $this->hasOne(Contact::class, 'id', 'student_id');
    }
    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'centre');
    }

    public function booku()
    {
        return $this->hasOne(bookuse::class, 'id', 'bookuse');
    }

}
