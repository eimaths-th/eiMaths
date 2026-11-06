<?php

namespace Tests\Feature\Guest;

use App\Models\Contact;
use App\Models\Department;
use App\Models\GuestQuizzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.recaptcha.enabled' => false]);
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
        Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }

    private function SubmitStudentLogin(string $code, string $password = 'wrong-password')
    {
        return $this->from('/students-login')->post(route('student.check'), ['code' => $code, 'password' => $password]);
    }

    private function SubmitGuestLogin(string $email, string $password = 'wrong-password')
    {
        return $this->from('/students-login')->post(route('guest.quiz.login'), [
            'guest_email' => $email,
            'guest_password' => $password,
        ]);
    }

    private function SubmitRegistration(int $attempt_number)
    {
        return $this->from('/free-quiz/register')->post(route('guest.quiz.register.store'), [
            'first_name' => 'Guest',
            'last_name' => 'Tester',
            'grade' => 'P1',
            'email' => "guest{$attempt_number}@example.com",
            'phone' => '0812345678',
            'pdpa' => '1',
            'privacy_notice_version' => 'p1-3-v1',
        ]);
    }

    #[Test]
    public function LimitsRepeatedAttemptsOnOneStudentAccount(): void
    {
        for ($attempt_number = 1; $attempt_number <= 6; $attempt_number++) {
            $this->SubmitStudentLogin('BR0001')->assertSessionHasErrors(['error' => __('students.login.invalid_credentials')]);
        }

        $this->SubmitStudentLogin('BR0001', 'correct-password')
            ->assertRedirect('/students-login')
            ->assertSessionHasErrors(['error' => __('students.login.rate_limited')])
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest('student');
    }

    #[Test]
    public function KeepsAnotherStudentAtTheSameAddressWorking(): void
    {
        for ($attempt_number = 1; $attempt_number <= 7; $attempt_number++) {
            $this->SubmitStudentLogin('BR0001');
        }

        $this->SubmitStudentLogin('BR0002')->assertSessionHasErrors(['error' => __('students.login.invalid_credentials')]);
    }

    #[Test]
    public function LimitsOneAddressAcrossManyStudentAccounts(): void
    {
        for ($attempt_number = 1; $attempt_number <= 30; $attempt_number++) {
            $this->SubmitStudentLogin("CODE{$attempt_number}")->assertSessionHasErrors(['error' => __('students.login.invalid_credentials')]);
        }

        $this->SubmitStudentLogin('CODE31')->assertSessionHasErrors(['error' => __('students.login.rate_limited')]);
    }

    #[Test]
    public function LimitsRepeatedAttemptsOnOneGuestAccount(): void
    {
        GuestQuizzer::create([
            'first_name' => 'Guest',
            'last_name' => 'Tester',
            'grade' => 'P1',
            'email' => 'guest@example.com',
            'phone' => '0812345678',
            'password' => Hash::make('0812345678'),
            'expires_at' => now()->addDays(10),
        ]);

        for ($attempt_number = 1; $attempt_number <= 6; $attempt_number++) {
            $this->SubmitGuestLogin('Guest@example.com');
        }

        $this->SubmitGuestLogin('guest@example.com', '0812345678')
            ->assertSessionHasErrors(['guest_error' => __('students.login.rate_limited')])
            ->assertSessionMissing('_old_input.guest_password');

        $this->assertGuest('guest_quiz');
    }

    #[Test]
    public function LimitsRegistrationsFromOneAddress(): void
    {
        for ($attempt_number = 1; $attempt_number <= 5; $attempt_number++) {
            $this->SubmitRegistration($attempt_number);
            auth('guest_quiz')->logout();
        }

        $this->SubmitRegistration(6)->assertSessionHasErrors(['registration' => __('students.login.rate_limited')]);

        $this->assertSame(5, GuestQuizzer::query()->count());
    }
}
