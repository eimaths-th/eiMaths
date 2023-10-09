<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'status',
    ];

     /**
     * Get department code by department ID.
     *
     * @param int $departmentId
     * @return string|null
     */
    public static function getDepartmentCodeById($departmentId)
    {
        $department = self::find($departmentId);

        if ($department) {
            return $department->code;
        }

        return null;
    }

    public function histrories() {
        return $this->hasMany(Histrories::class, 'student_id');
    }
}
