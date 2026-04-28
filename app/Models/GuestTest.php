<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $guest_quizzer_id
 * @property integer $quiz_id
 * @property integer $result
 * @property integer $total_questions
 * @property integer $time_spent
 * @property string $ip_address
 * @property string $created_at
 * @property string $updated_at
 * @property GuestQuizzer $guestQuizzer
 * @property Quiz $quiz
 * @property GuestAnswer[] $guestAnswers
 */
class GuestTest extends Model
{
    use HasFactory;

    protected $table = 'guest_tests';

    protected $fillable = [
        'guest_quizzer_id',
        'quiz_id',
        'result',
        'total_questions',
        'time_spent',
        'ip_address',
    ];

    public function guestQuizzer()
    {
        return $this->belongsTo(GuestQuizzer::class, 'guest_quizzer_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function guestAnswers()
    {
        return $this->hasMany(GuestAnswer::class, 'guest_test_id');
    }
}
