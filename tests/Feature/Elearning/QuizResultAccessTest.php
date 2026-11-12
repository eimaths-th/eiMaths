<?php

namespace Tests\Feature\Elearning;

use App\Models\{ContactQuiz, Department};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Support\QuizResultFixtures;
use Tests\TestCase;

class QuizResultAccessTest extends TestCase
{
    use RefreshDatabase, QuizResultFixtures {
        QuizResultFixtures::beforeRefreshingDatabase insteadof RefreshDatabase;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->PrepareResultFixtures();
    }

    #[Test]
    public function SeparatesListAndDetailCapabilities(): void
    {
        $participant = $this->CreateResultActor();
        $attempt = $this->CreateResultAttempt('user', $participant->id);
        foreach ([
            [[], 403, 403],
            [['quiz-results-list'], 200, 403],
            [['quiz-results-view'], 403, 200],
            [['quiz-results-list', 'quiz-results-view'], 200, 200],
        ] as [$permissions, $list_status, $detail_status]) {
            $actor = $this->CreateResultActor($permissions);
            $this->actingAs($actor, 'web');
            $this->get('/backoffice/quiz-results')->assertStatus($list_status);
            $this->get('/backoffice/quiz-results/attempts/'.$attempt->id)->assertStatus($detail_status);
        }
    }

    #[Test]
    public function ScopesBothParticipantTypesEvenWhenIdsCollide(): void
    {
        $other_user = $this->CreateResultActor([], 3);
        $student = $this->CreateResultStudent($other_user->id, 2);
        $student_attempt = $this->CreateResultAttempt('student', $student->id);
        $user_attempt = $this->CreateResultAttempt('user', $other_user->id, 43);
        $local_user = $this->CreateResultActor([], 2);
        $other_student = $this->CreateResultStudent($local_user->id, 3);
        $this->CreateResultAttempt('student', $other_student->id, 44);
        $actor = $this->CreateResultActor(['quiz-results-list', 'quiz-results-view']);
        $this->actingAs($actor);
        $this->get('/backoffice/quiz-results')->assertOk()->assertSee('Learner '.$student->id)->assertDontSee('#43');
        $this->get('/backoffice/quiz-results/attempts/'.$student_attempt->id)->assertOk();
        $this->get('/backoffice/quiz-results/attempts/'.$user_attempt->id)->assertNotFound();
        $this->get('/backoffice/quiz-results/attempts/44')->assertNotFound();
        $this->get('/backoffice/quiz-results/attempts/99999')->assertNotFound();
        $this->get('/results/'.$user_attempt->id)->assertNotFound();
    }

    #[Test]
    public function PreservesCrossBranchAndCentralModuleRules(): void
    {
        $participant = $this->CreateResultActor([], 3);
        $attempt = $this->CreateResultAttempt('user', $participant->id);
        $actor = $this->CreateResultActor(['quiz-results-view', 'all-centre']);
        $this->actingAs($actor)->get('/backoffice/quiz-results/attempts/42')->assertNotFound();
        $central = $this->CreateResultActor(['quiz-results-view', 'all-centre'], 1);
        $central->assignRole(Role::findOrCreate('Admin', 'web'));
        Department::find(1)->update(['enabled_modules' => []]);
        $this->actingAs($central)->get('/backoffice/quiz-results/attempts/'.$attempt->id)->assertOk();
    }

    #[Test]
    public function DeniesDisabledAccountsBranchesAndModules(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $actor->update(['disable' => 1]);
        $this->actingAs($actor)->get('/backoffice/quiz-results')->assertForbidden();
        $actor->update(['disable' => 0]);
        Department::find(2)->update(['status' => 0]);
        $this->get('/backoffice/quiz-results')->assertForbidden();
        Department::find(2)->update(['status' => 1, 'enabled_modules' => []]);
        $this->get('/backoffice/quiz-results')->assertForbidden();
        $actor->update(['department_id' => null]);
        $this->get('/backoffice/quiz-results')->assertForbidden();
    }

    #[Test]
    public function DeniesGuestsStudentsAndDualGuardStaffAccess(): void
    {
        $this->get('/backoffice/quiz-results')->assertRedirect(route('login'));
        $actor = $this->CreateResultActor(['quiz-results-list', 'quiz-results-view']);
        $student = $this->CreateResultStudent($actor->id);
        $attempt = $this->CreateResultAttempt('student', $student->id);
        $this->LoginResultStudent($student);
        $this->get('/backoffice/quiz-results')->assertRedirect(route('login'));
        $this->actingAs($actor, 'web');
        $this->get('/backoffice/quiz-results')->assertForbidden();
        $this->get('/backoffice/quiz-results/attempts/'.$attempt->id)->assertForbidden();
        $this->get('/results/'.$attempt->id)->assertOk();
    }

    #[Test]
    public function PreservesPersonalOwnershipAndStudentVideoAssignments(): void
    {
        $owner = $this->CreateResultActor(['quiz-list']);
        $student = $this->CreateResultStudent($owner->id);
        $user_attempt = $this->CreateResultAttempt('user', $owner->id);
        $student_attempt = $this->CreateResultAttempt('student', $student->id, 43);
        $this->actingAs($owner)->get('/results/42')->assertOk();
        $this->get('/results/43')->assertForbidden();
        Auth::guard('web')->logout();
        $this->LoginResultStudent($student);
        $this->get('/results/42')->assertForbidden();
        $this->get('/results/43')->assertOk()->assertDontSee('drive.google.com/file/d/result-video-id/preview');
        ContactQuiz::create(['contact_id' => $student->id, 'quiz_id' => $student_attempt->quiz_id]);
        $this->get('/results/43')->assertOk()->assertSee('drive.google.com/file/d/result-video-id/preview');
    }

    #[Test]
    public function LegacyStaffDetailRequiresNewPermission(): void
    {
        $participant = $this->CreateResultActor();
        $attempt = $this->CreateResultAttempt('user', $participant->id);
        $actor = $this->CreateResultActor(['quiz-list']);
        $this->actingAs($actor)->get('/results/'.$attempt->id)->assertForbidden();
        $actor = $this->CreateResultActor(['quiz-results-view']);
        $this->actingAs($actor)->get('/results/'.$attempt->id)->assertRedirect('/backoffice/quiz-results/attempts/42');
    }
}
