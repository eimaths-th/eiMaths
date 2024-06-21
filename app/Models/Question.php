<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property string $code
 * @property string $text
 * @property string $code_snippet
 * @property string $answer_explanation
 * @property string $more_info_link
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Answer[] $answers
 * @property Option[] $options
 * @property Quiz[] $quizzes
 */
class Question extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['code','question_text', 'code_snippet', 'answer_explanation', 'more_info_link', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function options()
    // {
    //     return $this->hasMany('App\Models\Option');
    // }

    // /**
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    //  */
    // public function quizzes()
    // {
    //     return $this->belongsToMany('App\Models\Quiz');
    // }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->inRandomOrder();
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class);
    }
}
