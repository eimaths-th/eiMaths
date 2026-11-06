<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentLoginEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
    }

    #[Test]
    public function DeletingAStudentDeletesTheirLoginEvents(): void
    {
        $student = $this->CreateStudent();
        DB::table('student_login_events')->insert([
            'contact_id' => $student->id, 'event_type' => 'LOGIN', 'occurred_at' => now(),
        ]);

        $student->delete();

        $this->assertDatabaseCount('student_login_events', 0);
    }

    #[Test]
    public function SuccessfulLoginRecordsOneLoginEvent(): void
    {
        $student = $this->CreateStudent();

        $this->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password'])
            ->assertRedirect(route('home3'));

        $this->assertSame(
            [[$student->id, 'LOGIN']],
            DB::table('student_login_events')->get()->map(fn ($event) => [(int) $event->contact_id, $event->event_type])->all()
        );
    }

    #[Test]
    public function FailedLoginRecordsNothing(): void
    {
        $this->CreateStudent();

        $this->from('/students-login')
            ->post('/students-login-check', ['code' => 'BR0001', 'password' => 'incorrect-password'])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseCount('student_login_events', 0);
    }

    #[Test]
    public function LogoutRecordsOneLogoutEventForThatStudent(): void
    {
        $student = $this->CreateStudent();
        $this->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password']);

        $this->post('/students-logout')->assertRedirect(route('student.login'));

        $this->assertDatabaseHas('student_login_events', ['contact_id' => $student->id, 'event_type' => 'LOGOUT']);
        $this->assertDatabaseCount('student_login_events', 2);
    }

    #[Test]
    public function LoginSucceedsWhenTheEventCannotBeWritten(): void
    {
        $student = $this->CreateStudent();
        Schema::drop('student_login_events');
        Log::spy();

        $this->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password'])
            ->assertRedirect(route('home3'));

        $this->assertAuthenticatedAs($student, 'student');
        Log::shouldHaveReceived('warning')->once()->with('STUDENT_LOGIN_EVENT_FAILED', [
            'contact_id' => $student->id, 'event_type' => 'LOGIN',
        ]);
    }

    #[Test]
    public function LogoutSucceedsWhenTheEventCannotBeWritten(): void
    {
        $this->CreateStudent();
        $this->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password']);
        Schema::drop('student_login_events');

        $this->post('/students-logout')->assertRedirect(route('student.login'));

        $this->assertGuest('student');
    }

    #[Test]
    public function LoginIsRecordedWhileStaffFromTheSameBranchIsSignedInOnTheSameBrowser(): void
    {
        $staff = User::factory()->create(['department_id' => 2, 'disable' => 0, 'password' => bcrypt('secret')]);
        $student = $this->CreateStudent();

        $this->actingAs($staff)
            ->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password'])
            ->assertRedirect(route('home3'));

        $this->assertDatabaseHas('student_login_events', ['contact_id' => $student->id, 'event_type' => 'LOGIN']);
    }

    #[Test]
    public function NoEventIsRecordedWhenAnOtherBranchStaffSessionHidesTheStudent(): void
    {
        Department::forceCreate(['id' => 3, 'name' => 'Other', 'code' => 'OT', 'status' => 1]);
        $staff = User::factory()->create(['department_id' => 3, 'disable' => 0, 'password' => bcrypt('secret')]);
        $this->CreateStudent();

        $this->actingAs($staff)
            ->from('/students-login')
            ->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password'])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseCount('student_login_events', 0);
    }

    private function CreateStudent(): Contact
    {
        return Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }
}
