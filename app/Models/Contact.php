<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use App\Exports\ContactsExport;

/**
 * @property integer $id
 * @property integer $ctype
 * @property integer $order
 * @property integer $centre
 * @property string $code
 * @property integer $free_course
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
    protected $fillable = ['ref', 'ctype', 'order', 'centre', 'code', 'free_course', 'type', 'start_date', 'start_term', 'name', 'nickname', 'school', 'gender', 'birth_date', 'password', 'address', 'postcode', 'telephone', 'father_name', 'father_email', 'father_mobile', 'mother_name', 'mother_email', 'mother_mobile', 'level', 'term', 'bookuse', 'level_name', 'term_name', 'bookuse_name', 'level2', 'term2', 'bookuse2', 'level2_name', 'term2_name', 'bookuse2_name', 'discontinued', 'discontinued_date', 'discontinued_reason', 'created_by', 'updated_by', 'created_at', 'updated_at'];

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

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class)
            ->withPivot('assigned_at', 'status')
            ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'centre', 'id');
    }

    /**
     * Query contacts by centre (2, 3) and group by centre, order by id desc
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getContactsByCentre()
    {
        return self::whereIn('centre', [2, 3])
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('centre');
    }

    /**
     * Export contacts data to Excel for centres 2 and 3
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function exportToExcel($filename = 'contacts_export.xlsx')
    {
        $contacts = self::whereIn('centre', [2, 3])
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('centre');

        // Create Excel file using Laravel Excel package
        return \Maatwebsite\Excel\Facades\Excel::download(new ContactsExport($contacts), $filename);
    }
}
