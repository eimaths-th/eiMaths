<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $guest_test_id
 * @property integer $question_id
 * @property integer $option_id
 * @property integer $image_id
 * @property string $written_answer
 * @property string $fraction
 * @property boolean $correct
 * @property string $created_at
 * @property string $updated_at
 * @property GuestTest $guestTest
 * @property Question $question
 * @property Option $option
 * @property ImageOption $image
 */
class GuestAnswer extends Model
{
    use HasFactory;

    protected $table = 'guest_answers';

    protected $fillable = [
        'guest_test_id',
        'question_id',
        'option_id',
        'image_id',
        'written_answer',
        'fraction',
        'correct',
    ];

    public function guestTest()
    {
        return $this->belongsTo(GuestTest::class, 'guest_test_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

    public function image()
    {
        return $this->belongsTo(ImageOption::class, 'image_id');
    }
}
