<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $centre // ศูนย์การศึกษา
 * @property integer $student_id // รหัสนักเรียน
 * @property integer $level_id // รหัสชั้น
 * @property string $level_name // ชั้น
 * @property string $term // ภาคเรียน
 * @property string $bookuse // หนังสือ
 * @property string $course_remaining // จำนวนครั้งที่เหลือ
 * @property string $date // วันที่มาเรียน
 * @property string $stime // เวลาเริ่ม
 * @property string $etime // เวลาสิ้นสุด
 * @property string $start_date // วันที่เริ่มเรียน
 * @property string $end_date // วันที่สิ้นสุด
 * @property string $comment // หมายเหตุ
 * @property string $signature // ลายเซ็นต์
 * @property string $created_at // วันที่สร้าง
 * @property string $updated_at // วันที่แก้ไข
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
