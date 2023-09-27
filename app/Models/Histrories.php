<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
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
    protected $fillable = ['student_id', 'level_id', 'term', 'bookuse', 'course_remaining', 'date', 'stime', 'etime', 'start_course', 'comment', 'signature', 'created_at', 'updated_at'];
}
