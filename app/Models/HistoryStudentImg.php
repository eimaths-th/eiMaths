<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $history_id
 * @property string $student_id
 * @property string $img
 * @property string $created_at
 * @property string $updated_at
 */
class HistoryStudentImg extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'history_student_img';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['history_id', 'student_id', 'img', 'created_at', 'updated_at'];
}
