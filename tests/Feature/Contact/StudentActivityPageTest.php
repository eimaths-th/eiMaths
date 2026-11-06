<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\StudentLoginEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\QuizResultFixtures;
use Tests\TestCase;

class StudentActivityPageTest extends TestCase
{
    use RefreshDatabase, QuizResultFixtures {
        QuizResultFixtures::beforeRefreshingDatabase insteadof RefreshDatabase;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->PrepareResultFixtures();
        Permission::findOrCreate('student-activity-list', 'web');
        Permission::findOrCreate('student-list', 'web');
    }

    #[Test]
    public function BranchStaffReadLoginHistoryForTheirOwnStudent(): void
    {
        $student = $this->CreateResultStudent(501, 2);
        StudentLoginEvent::forceCreate(['contact_id' => $student->id, 'event_type' => 'LOGIN', 'occurred_at' => '2026-11-06 09:12:00']);

        $this->actingAs($this->CreateResultActor(['student-activity-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertSee('Learner 501')
            ->assertSee('06/11/2026 09:12');
    }

    #[Test]
    public function StaffWithoutThePermissionAreRefused(): void
    {
        $student = $this->CreateResultStudent(501, 2);

        $this->actingAs($this->CreateResultActor(['student-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertForbidden();
    }

    #[Test]
    public function BranchStaffCannotReadAStudentFromAnotherBranch(): void
    {
        $student = $this->CreateResultStudent(502, 3);

        $this->actingAs($this->CreateResultActor(['student-activity-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertNotFound();
    }

    #[Test]
    public function CentralStaffReadAStudentFromAnyBranch(): void
    {
        $student = $this->CreateResultStudent(502, 3);
        $admin = User::factory()->create(['department_id' => 1, 'disable' => 0, 'password' => 'result-test-password']);
        $admin->assignRole(Role::findOrCreate('Admin', 'web'));
        $admin->givePermissionTo(['student-activity-list', 'all-centre']);

        $this->actingAs($admin)->get(route('contacts.activity.index', $student->id))->assertOk();
    }

    #[Test]
    public function AStudentWithoutHistoryShowsTheEmptyState(): void
    {
        $student = $this->CreateResultStudent(501, 2);

        $this->actingAs($this->CreateResultActor(['student-activity-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertSee(__('students.activity.no_logins'));
    }

    #[Test]
    public function QuizSectionShowsScoreAndAnswerLinkForPermittedStaff(): void
    {
        $student = $this->CreateStudentWithQuizAttemptSupport();
        $attempt = $this->CreateResultAttempt('student', $student->id);

        $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list', 'quiz-results-view'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertSee('Addition practice')
            ->assertSee('1/1')
            ->assertSee('00:01:02')
            ->assertSee(route('backoffice.quiz-results.show', $attempt), false);
    }

    #[Test]
    public function QuizSectionIsHiddenWithoutQuizResultsList(): void
    {
        $student = $this->CreateStudentWithQuizAttemptSupport();
        $this->CreateResultAttempt('student', $student->id);

        $this->actingAs($this->CreateResultActor(['student-activity-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertDontSee('Addition practice');
    }

    #[Test]
    public function AnswerLinkIsHiddenWithoutQuizResultsView(): void
    {
        $student = $this->CreateStudentWithQuizAttemptSupport();
        $attempt = $this->CreateResultAttempt('student', $student->id);

        $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertSee('Addition practice')
            ->assertDontSee(route('backoffice.quiz-results.show', $attempt), false);
    }

    #[Test]
    public function QuizSectionExcludesStaffAttemptsWithTheSameId(): void
    {
        $student = $this->CreateStudentWithQuizAttemptSupport();
        $this->CreateResultAttempt('user', 501);

        $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertDontSee('Addition practice');
    }

    #[Test]
    public function TablesLabelTheirColumns(): void
    {
        $student = $this->CreateStudentWithQuizAttemptSupport();
        StudentLoginEvent::forceCreate(['contact_id' => $student->id, 'event_type' => 'LOGIN', 'occurred_at' => '2026-11-06 09:12:00']);
        $this->CreateResultAttempt('student', $student->id);

        $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list'], 2))
            ->get(route('contacts.activity.index', $student->id))
            ->assertOk()
            ->assertSee('<th scope="col">Event</th>', false)
            ->assertSee('<th scope="col">Score</th>', false);
    }

    #[Test]
    public function StudentListShowsTheActivityLinkOnlyWithThePermission(): void
    {
        $student = $this->CreateResultStudent(501, 2);
        $activity_url = route('contacts.activity.index', $student->id);

        $permitted_action = $this->ReadStudentListAction($this->CreateResultActor(['student-list', 'student-activity-list'], 2));
        $this->assertStringContainsString($activity_url, $permitted_action);

        $refused_action = $this->ReadStudentListAction($this->CreateResultActor(['student-list'], 2));
        $this->assertStringNotContainsString($activity_url, $refused_action);
    }

    #[Test]
    public function MigrationGrantsAdministratorsAndNotTeachers(): void
    {
        Permission::where('name', 'student-activity-list')->delete();
        foreach (['Admin', 'BranchAdmin', 'Teacher'] as $role_name) {
            Role::findOrCreate($role_name, 'web');
        }
        $migration = require database_path('migrations/2026_11_07_000002_register_student_activity_permission.php');

        $migration->up();

        $this->assertTrue(Role::findByName('Admin', 'web')->hasPermissionTo('student-activity-list'));
        $this->assertTrue(Role::findByName('BranchAdmin', 'web')->hasPermissionTo('student-activity-list'));
        $this->assertFalse(Role::findByName('Teacher', 'web')->hasPermissionTo('student-activity-list'));

        $migration->down();

        $this->assertFalse(Permission::where('name', 'student-activity-list')->exists());
    }

    // tests.user_id references users, so a student attempt needs a user row with the same ID.
    private function CreateStudentWithQuizAttemptSupport(): Contact
    {
        User::factory()->create(['id' => 501, 'department_id' => 2, 'disable' => 0, 'password' => 'result-test-password']);

        return $this->CreateResultStudent(501, 2);
    }

    private function ReadStudentListAction(User $actor): string
    {
        return $this->actingAs($actor)
            ->getJson('/contacts?draw=1&start=0&length=10', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->json('data.0.action');
    }
}
