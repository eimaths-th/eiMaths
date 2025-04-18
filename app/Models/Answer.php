<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $test_id
 * @property integer $question_id
 * @property integer $option_id
 * @property integer $image_id
 * @property string $type_user
 * @property string $written_answer
 * @property boolean $correct
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Option $option
 * @property Test $test
 * @property Question $question
 * @property User $user
 */
class Answer extends Model
{
    /**
     * @var array
     */
    protected $table = 'answers';

    protected $fillable = [
        'user_id',
        'test_id',
        'question_id',
        'type_user',
        'option_id',
        'image_id',
        'fraction',
        'written_answer',
        'correct',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function option()
    {
        return $this->belongsTo('App\Models\Option');
    }
    public function image()
    {
        return $this->belongsTo('App\Models\ImageOption');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function test()
    {
        return $this->belongsTo('App\Models\Test');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
