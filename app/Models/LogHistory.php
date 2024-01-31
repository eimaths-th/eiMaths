<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $centre
 * @property integer $history_id
 * @property integer $student_id
 * @property integer $level_id_old
 * @property string $level_name_old
 * @property string $term_old
 * @property string $bookuse_old
 * @property string $course_remaining_old
 * @property string $date_old
 * @property string $stime_old
 * @property string $etime_old
 * @property string $comment_old
 * @property integer $level_id_new
 * @property string $level_name_new
 * @property string $term_new
 * @property string $bookuse_new
 * @property string $course_remaining_new
 * @property string $date_new
 * @property string $stime_new
 * @property string $etime_new
 * @property string $comment_new
 * @property integer $approve_id
 * @property string $approve_name
 * @property string $created_at
 * @property string $updated_at
 */
class LogHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'history_log';

    /**
     * @var array
     */
    protected $fillable = ['centre', 'history_id', 'student_id', 'level_id_old', 'level_name_old', 'term_old', 'bookuse_old', 'course_remaining_old', 'date_old', 'stime_old', 'etime_old', 'comment_old', 'level_id_new', 'level_name_new', 'term_new', 'bookuse_new', 'course_remaining_new', 'date_new', 'stime_new', 'etime_new', 'comment_new', 'approve_id','approve_name', 'created_at', 'updated_at'];

}
