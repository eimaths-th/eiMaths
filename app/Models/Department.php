<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    public const HQ_ID = 1;

    protected $casts = ['enabled_modules' => 'array'];

    protected $fillable = [
        'name',
        'code',
        'status',
        'enabled_modules',
        'branch_type',
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
        return $this->hasMany(Histrories::class, 'centre');
    }

    public function BookShipments(): HasMany
    {
        return $this->hasMany(BookShipment::class, 'destination_centre_id');
    }
}
