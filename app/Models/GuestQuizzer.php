<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $nickname
 * @property string $grade
 * @property string $school
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $expires_at
 * @property string $created_at
 * @property string $updated_at
 * @property GuestTest[] $guestTests
 */
class GuestQuizzer extends Authenticatable
{
    use HasFactory;

    protected $table = 'guest_quizzers';

    protected $fillable = [
        'first_name',
        'last_name',
        'nickname',
        'grade',
        'school',
        'email',
        'phone',
        'password',
        'expires_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * ตรวจว่าหมดอายุหรือยัง
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * ชื่อเต็ม
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * ผลสอบทั้งหมด
     */
    public function guestTests()
    {
        return $this->hasMany(GuestTest::class, 'guest_quizzer_id');
    }

    /**
     * ลบข้อมูลการสอบทั้งหมด (เมื่อหมดอายุ)
     */
    public function deleteTestData(): void
    {
        $this->guestTests()->each(function ($test) {
            $test->guestAnswers()->delete();
        });
        $this->guestTests()->delete();
    }
}
