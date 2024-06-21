<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $question_id
 * @property integer $quiz_id
 * @property Question $question
 * @property Quiz $quiz
 */
class QuestionQuiz extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'question_quiz';

    /**
     * @var array
     */
    protected $fillable = ['question_id', 'quiz_id'];

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
    public function quiz()
    {
        return $this->belongsTo('App\Models\Quiz');
    }
}
