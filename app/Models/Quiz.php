<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property string $slug
 * @property string $level
 * @property string $term
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
    protected $fillable = ['title', 'slug', 'level', 'term','description', 'published', 'public', 'created_at', 'updated_at', 'deleted_at'];

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
        return $this->belongsToMany(Question::class);
    }

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
