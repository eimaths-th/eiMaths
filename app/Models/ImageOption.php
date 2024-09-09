<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $question_id
 * @property string $img_name
 * @property boolean $correct
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Question $question
 */
class ImageOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'img_options';

    /**
     * @var array
     */
    protected $fillable = ['question_id', 'img_name', 'correct', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
