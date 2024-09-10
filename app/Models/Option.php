<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $question_id
 * @property string $text
 * @property boolean $correct
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Answer[] $answers
 * @property Question $question
 */
class Option extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var array
     */
    protected $table = 'options';

    protected $fillable = ['question_id', 'text', 'correct', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
