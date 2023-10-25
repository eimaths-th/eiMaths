<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $student_id
 * @property string $centre_id
 * @property string $fname
 * @property string $lname
 * @property string $telp
 * @property string $email
 * @property string $password
 * @property string $address
 * @property string $relationship
 * @property string $gender
 * @property string $emergency_contact
 * @property string $notes
 * @property string $created_at
 * @property string $updated_at
 */
class Parents extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    protected $table = 'parents';
    /**
     * @var array
     */
    protected $fillable = ['student_id', 'centre_id', 'fname', 'lname', 'telp', 'email', 'password', 'address', 'relationship', 'gender', 'emergency_contact', 'notes', 'created_at', 'updated_at'];

    public function students()
    {
        $studentIds = explode(',', $this->student_id);
        return Contact::whereIn('id', $studentIds)->get();
    }

    public function getFullNameAttribute()
    {
        return $this->fname . ' ' . $this->lname;
    }
    public function getTelpEmailAttribute()
    {
        return $this->telp . ' / ' . $this->email;
    }
    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'centre_id');
    }

}
