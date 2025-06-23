<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $question_text
 * @property integer $answer_numerator
 * @property integer $answer_denominator
 * @property string $created_at
 * @property string $updated_at
 */
class TestSystemQuestions extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'test_sys_questions';

    /**
     * @var array
     */
    protected $fillable = ['question_text', 'answer_numerator', 'answer_denominator', 'created_at', 'updated_at'];
}
