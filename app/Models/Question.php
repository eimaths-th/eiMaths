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
 * @property string $level
 * @property string $term
 * @property string $section
 * @property string $code_snippet
 * @property string $answer_explanation
 * @property string $more_info_link
 * @property string $written_answer
 * @property string $type
 * @property string $img_name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $created_by
 * @property string $updated_by
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
    protected $table = 'questions';
    protected $fillable = [
        'code',
        'text',
        'level',
        'term',
        'section',
        'code_snippet',
        'answer_explanation',
        'answer_explanation_image',
        'more_info_link',
        'written_answer',
        'type',
        'img_name',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

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

    public function fractions(): HasMany
    {
        return $this->hasMany(FactionAnswer::class)->inRandomOrder();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImageOption::class)->inRandomOrder();
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class);
    }
}
