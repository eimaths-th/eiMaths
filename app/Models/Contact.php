<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

/**
 * @property integer $id
 * @property integer $ctype
 * @property integer $order
 * @property integer $centre
 * @property string $code
 * @property string $type
 * @property string $start_date
 * @property integer $start_term
 * @property string $name
 * @property string $nickname
 * @property string $school
 * @property integer $gender
 * @property string $birth_date
 * @property string $password
 * @property string $address
 * @property string $postcode
 * @property string $telephone
 * @property string $father_name
 * @property string $father_email
 * @property string $father_mobile
 * @property string $mother_name
 * @property string $mother_email
 * @property string $mother_mobile
 * @property integer $level
 * @property integer $term
 * @property integer $bookuse
 * @property string $level_name
 * @property string $term_name
 * @property string $bookuse_name
 * @property integer $level2
 * @property integer $term2
 * @property integer $bookuse2
 * @property string $level2_name
 * @property string $term2_name
 * @property string $bookuse2_name
 * @property integer $discontinued
 * @property string $discontinued_date
 * @property string $discontinued_reason
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property ParentStudent[] $parentStudents
 */
class Contact extends Model implements AuthenticatableContract
{
    use Authenticatable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';

    /**
     * @var array
     */
    protected $fillable = ['ctype', 'order', 'centre', 'code','type', 'start_date', 'start_term', 'name', 'nickname', 'school', 'gender', 'birth_date', 'password','address', 'postcode', 'telephone', 'father_name', 'father_email', 'father_mobile', 'mother_name', 'mother_email', 'mother_mobile', 'level', 'term', 'bookuse', 'level_name', 'term_name', 'bookuse_name', 'level2', 'term2', 'bookuse2', 'level2_name', 'term2_name', 'bookuse2_name', 'discontinued', 'discontinued_date', 'discontinued_reason', 'created_by', 'updated_by', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parentStudents()
    {
        return $this->hasMany('App\Models\ParentStudent', 'student_id');
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'cid', 'id');
    }

    public function histrories()
    {
        return $this->hasMany(Histrories::class, 'centre');
    }

    public function getStudentCodeAttribute()
    {

        return $this->code . ' ' . $this->name;
    }
}
