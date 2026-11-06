<?php

namespace Tests\Support;

use App\Domain\Elearning\StudentSessionService;
use App\Models\{Answer, Contact, Department, Question, Quiz, Test as QuizTest, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http, Mail, Notification};
use Spatie\Permission\Models\{Permission, Role};

trait QuizResultFixtures
{
    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    private function PrepareResultFixtures(): void
    {
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->withoutVite();
        foreach ([1 => 'HQ', 2 => 'Branch A', 3 => 'Branch B'] as $branch_id => $branch_name) {
            Department::forceCreate(['id' => $branch_id, 'name' => $branch_name, 'code' => 'QR'.$branch_id, 'status' => 1, 'enabled_modules' => ['quiz', 'users', 'students']]);
        }
        foreach (['quiz-manage', 'quiz-list', 'quiz-results-list', 'quiz-results-view', 'all-centre'] as $permission_name) {
            Permission::findOrCreate($permission_name, 'web');
        }
    }

    private function CreateResultActor(array $permission_names = [], int $branch_id = 2): User
    {
        $actor = User::factory()->create(['department_id' => $branch_id, 'disable' => 0, 'password' => 'result-test-password']);
        $role = Role::findOrCreate('ResultReviewer'.$actor->id, 'web');
        $role->syncPermissions($permission_names);
        $actor->assignRole($role);
        return $actor;
    }

    private function CreateResultAttempt(string $participant_type, int $participant_id, int $attempt_id = 42): QuizTest
    {
        $quiz = Quiz::firstOrCreate(['slug' => 'addition'], ['title' => 'Addition practice', 'level' => 'P1', 'term' => '1', 'section' => '2']);
        $question = Question::forceCreate(['text' => 'One plus one?', 'answer_explanation' => 'Two', 'type' => 'written', 'answer_explanation_video_url' => 'https://drive.google.com/file/d/result-video-id/view']);
        $quiz->questions()->attach($question->id);
        $attempt = QuizTest::forceCreate(['id' => $attempt_id, 'type_user' => $participant_type, 'user_id' => $participant_id, 'quiz_id' => $quiz->id, 'result' => 1, 'time_spent' => 62, 'created_at' => '2026-11-05 10:00:00']);
        Answer::forceCreate(['test_id' => $attempt->id, 'user_id' => $participant_id, 'type_user' => $participant_type, 'question_id' => $question->id, 'correct' => true, 'written_answer' => '2']);
        return $attempt;
    }

    private function CreateResultStudent(int $student_id, int $branch_id = 2): Contact
    {
        return Contact::forceCreate(['id' => $student_id, 'centre' => $branch_id, 'name' => 'Result Student '.$student_id, 'nickname' => 'Learner '.$student_id, 'code' => 'QR'.$student_id, 'type' => 'student', 'password' => bcrypt('result-test-password')]);
    }

    private function LoginResultStudent(Contact $student): void
    {
        $this->actingAs($student, 'student');
        $request = Request::create('/results');
        $request->setLaravelSession(session()->driver());
        app(StudentSessionService::class)->Start($student, $request);
        Auth::shouldUse('web');
    }
}
