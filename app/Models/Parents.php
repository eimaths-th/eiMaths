<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
class Parents extends Authenticatable
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
    use HasApiTokens, Notifiable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id', 
        'centre_id', 
        'fname', 
        'lname', 
        'telp', 
        'email', 
        'password', 
        'address', 
        'relationship', 
        'gender', 
        'emergency_contact', 
        'notes'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function students()
    {
        return $this->studentRecords()->get();
    }

    public function studentRecords()
    {
        return $this->belongsToMany(Contact::class, 'parent_student', 'parent_id', 'student_id')->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saved(function (self $parent): void {
            if ($parent->wasRecentlyCreated || $parent->wasChanged('student_id')) {
                $ids = array_values(array_unique(array_filter(explode(',', (string) $parent->student_id), fn ($id) => ctype_digit($id))));
                $validIds = Contact::whereIn('id', $ids)->pluck('id')->all();
                $parent->studentRecords()->sync($validIds);
            }
        });
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
