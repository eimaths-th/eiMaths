<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $quiz_id
 * @property integer $contact_id
 * @property string $assigned_at
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class ContactQuiz extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'contact_quiz';

    /**
     * @var array
     */
    protected $fillable = ['quiz_id', 'contact_id', 'assigned_at', 'status', 'created_at', 'updated_at'];

    public function student()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
