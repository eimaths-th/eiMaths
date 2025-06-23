<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $question_id
 * @property string $type
 * @property string $text
 * @property string $answer_type
 * @property integer $numerator
 * @property integer $denominator
 * @property boolean $correct
 * @property string $created_at
 * @property string $updated_at
 */
class FactionAnswer extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'fraction_answers';

    /**
     * @var array
     */
    protected $fillable = [
        'question_id',
        'type',
        'text', //no use
        'answer_type',
        'numerator',
        'denominator',
        'correct',
        'created_at',
        'updated_at'
    ];
}
