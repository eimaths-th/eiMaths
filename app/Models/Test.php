<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $quiz_id
 * @property integer $result
 * @property integer $type_user
 * @property string $ip_address
 * @property integer $time_spent
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Answer[] $answers
 * @property Quiz $quiz
 * @property User $user
 */
class Test extends Model
{
    /**
     * @var array
     */
    protected $table = 'tests';

    protected $fillable = ['user_id', 'quiz_id', 'result', 'type_user', 'ip_address', 'time_spent', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function answers()
    // {
    //     return $this->hasMany('App\Models\Answer');
    // }
    public function answers()
    {
        return $this->hasMany(Answer::class, 'test_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Contact::class, 'user_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'answers', 'test_id', 'question_id');
    }
}
