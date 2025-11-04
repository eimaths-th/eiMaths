<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property string $slug
 * @property string $level
 * @property string $term
 * @property string $section
 * @property string $description
 * @property boolean $published
 * @property boolean $public
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Question[] $questions
 * @property Test[] $tests
 */
class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'title',
        'slug',
        'level',
        'term',
        'section',
        'description',
        'published',
        'public',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

    public function tests()
    {
        return $this->hasMany(Test::class, 'quiz_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class)
            ->withPivot('position')
            ->orderBy('question_quiz.position');
    }

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class)
            ->withPivot(
                'assigned_at',
                'status'
            )
            ->withTimestamps();
    }
}
