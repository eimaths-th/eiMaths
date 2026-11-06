<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use App\Models\Quiz;
use App\Domain\Elearning\StudentSessionService;
use App\Http\Middleware\EnsureCurrentStudentSession;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentElearningSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
    }

    #[Test]
    public function SuccessfulLoginStoresOnlyTheHashOfANewSessionToken(): void
    {
        $student = $this->CreateStudent();

        $this->post('/students-login-check', [
            'code' => 'BR0001', 'password' => 'correct-password',
        ])->assertRedirect(route('home3'));

        $session_token = session('elearning_session_token');
        $this->assertIsString($session_token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $session_token);
        $this->assertSame(hash('sha256', $session_token), $student->fresh()->elearning_session_token_hash);
        $this->assertArrayNotHasKey('elearning_session_token_hash', $student->fresh()->toArray());
        $this->assertAuthenticatedAs($student, 'student');
    }

    #[Test]
    public function NewLoginReplacesTheOldTokenAndInvalidPasswordCannotReplaceIt(): void
    {
        $student = $this->CreateStudent();
        $first_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $second_browser = $this->LoginStudent();
        $this->assertNotSame($first_browser['elearning_session_token'], $second_browser['elearning_session_token']);
        $current_hash = $student->fresh()->elearning_session_token_hash;
        $this->assertSame(hash('sha256', $second_browser['elearning_session_token']), $current_hash);

        $this->RestoreBrowser([]);
        $this->from('/students-login')->post('/students-login-check', [
            'code' => 'BR0001', 'password' => 'incorrect-password',
        ])->assertSessionHasErrors('error');
        $this->assertGuest('student');
        $this->assertSame($current_hash, $student->fresh()->elearning_session_token_hash);
    }

    #[Test]
    public function CurrentLogoutRevokesOnlyTheStudentSession(): void
    {
        $student = $this->CreateStudent();
        $this->LoginStudent();
        $staff = User::factory()->create(['department_id' => 2, 'disable' => 0, 'password' => bcrypt('staff-password')]);
        Auth::guard('web')->login($staff);
        session()->put('unrelated_state', 'preserve-me');
        session()->put('guest_answersOfQuestions', ['guest-answer']);
        session()->put('answersOfQuestions', ['student-answer']);
        session()->put('quizStartTime', 100);
        session()->put('startTimeInSeconds', 100);
        $this->RestoreBrowser(session()->all());

        $this->post('/students-logout')->assertRedirect(route('student.login'));

        $this->assertNull($student->fresh()->elearning_session_token_hash);
        $this->assertFalse(session()->has('elearning_session_token'));
        $this->assertSame('preserve-me', session('unrelated_state'));
        $this->assertSame(['guest-answer'], session('guest_answersOfQuestions'));
        $this->assertFalse(session()->has('answersOfQuestions'));
        $this->assertFalse(session()->has('quizStartTime'));
        $this->assertFalse(session()->has('startTimeInSeconds'));
        $this->app['auth']->forgetGuards();
        $this->assertGuest('student');
        $this->assertAuthenticatedAs($staff, 'web');
    }

    #[Test]
    public function ObsoleteLogoutCannotRevokeTheReplacement(): void
    {
        $student = $this->CreateStudent();
        $first_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $second_browser = $this->LoginStudent();

        $this->RestoreBrowser($first_browser);
        $this->post('/students-logout')->assertRedirect(route('student.login'));

        $this->assertFalse(session()->has('elearning_session_token'));
        $this->assertSame(hash('sha256', $second_browser['elearning_session_token']), $student->fresh()->elearning_session_token_hash);
    }

    #[Test]
    public function ObsoleteSessionCannotCheckAnswersButReplacementAndSharedTabsCan(): void
    {
        $student = $this->CreateStudent();
        $first_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $second_browser = $this->LoginStudent();
        $this->RestoreBrowser($first_browser);

        $this->postJson('/check-answer', $this->AnswerPayload())
            ->assertUnauthorized()->assertJsonPath('code', 'ELEARNING_SESSION_REPLACED')
            ->assertJsonPath('login_url', route('student.login'));
        $this->assertGuest('student');
        $this->assertSame(hash('sha256', $second_browser['elearning_session_token']), $student->fresh()->elearning_session_token_hash);

        foreach (range(1, 2) as $tab_number) {
            $this->RestoreBrowser($second_browser);
            $this->postJson('/check-answer', $this->AnswerPayload())
                ->assertOk()->assertJsonPath('checkCorrect', 1);
        }
    }

    #[Test]
    public function ObsoleteBrowserIsRedirectedAndTheLoginPageShowsTheReason(): void
    {
        $this->CreateStudent();
        $first_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $this->LoginStudent();
        $this->RestoreBrowser($first_browser);
        config(['ui-localization.default_locale' => 'en']);

        $this->get('/quiz-home')->assertRedirect(route('student.login'))->assertSessionHasErrors('error');
        $this->get('/students-login')->assertOk()
            ->assertSee('This account was signed in elsewhere. Please sign in again.');
    }

    #[Test]
    public function JsonReplacementResponseCanLeadToTheLocalizedLoginMessage(): void
    {
        $this->CreateStudent();
        $first_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $this->LoginStudent();
        $this->RestoreBrowser($first_browser);
        config(['ui-localization.default_locale' => 'th']);

        $response = $this->postJson('/check-answer', $this->AnswerPayload())
            ->assertUnauthorized()->assertJsonPath('message', 'บัญชีนี้เข้าสู่ระบบจากที่อื่น กรุณาเข้าสู่ระบบอีกครั้ง');
        $this->get($response->json('login_url'))->assertOk()
            ->assertSee('บัญชีนี้เข้าสู่ระบบจากที่อื่น กรุณาเข้าสู่ระบบอีกครั้ง');
    }

    #[Test]
    #[DataProvider('InvalidSessionTokens')]
    public function MissingOrInvalidTokensCannotAccessLearning(mixed $invalid_token): void
    {
        $this->CreateStudent();
        $browser = $this->LoginStudent();
        $browser['elearning_session_token'] = $invalid_token;
        $this->RestoreBrowser($browser);
        $this->postJson('/check-answer', $this->AnswerPayload())->assertUnauthorized();
    }

    public static function InvalidSessionTokens(): array
    {
        return ['missing' => [null], 'empty' => [''], 'wrong' => ['incorrect-token'], 'array' => [[]]];
    }

    #[Test]
    public function LegacyDatabaseHashCannotAuthorizeAnExistingBrowser(): void
    {
        $student = $this->CreateStudent();
        $browser = $this->LoginStudent();
        Contact::whereKey($student->id)->update(['elearning_session_token_hash' => null]);
        $this->RestoreBrowser($browser);
        $this->postJson('/check-answer', $this->AnswerPayload())->assertUnauthorized();
    }

    #[Test]
    public function ObsoleteStudentCannotFallThroughToStaffAndStaffRemainsSignedIn(): void
    {
        $this->CreateStudent();
        $browser = $this->LoginStudent();
        $staff = User::factory()->create(['department_id' => 2, 'disable' => 0, 'password' => bcrypt('staff-password')]);
        Auth::guard('web')->login($staff);
        $browser = session()->all();
        $this->RestoreBrowser([]);
        $this->LoginStudent();
        $this->RestoreBrowser($browser);

        $this->postJson('/check-answer', $this->AnswerPayload())->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->assertAuthenticatedAs($staff, 'web');
        $this->assertGuest('student');
        $this->postJson('/check-answer', $this->AnswerPayload())->assertOk()->assertJsonPath('checkCorrect', 1);
    }

    #[Test]
    public function ObsoleteSessionCannotSaveOrSubmitAnswers(): void
    {
        $this->CreateStudent();
        $quiz = Quiz::create([
            'title' => 'Session test quiz', 'slug' => 'session-test-quiz',
            'level' => 'P1', 'term' => '1', 'section' => '1',
        ]);
        $old_browser = $this->LoginStudent();
        $this->RestoreBrowser([]);
        $current_browser = $this->LoginStudent();
        $this->RestoreBrowser($old_browser);
        session()->put('answersOfQuestions', ['sentinel' => 'unchanged']);
        $this->postJson('/quiz/save-answer', ['quiz_id' => $quiz->id])->assertUnauthorized();
        $this->assertFalse(session()->has('answersOfQuestions'));

        $this->RestoreBrowser($old_browser);
        $this->postJson(route('quiz.submit', $quiz), [])->assertUnauthorized();
        $this->assertDatabaseCount('tests', 0);
        $this->RestoreBrowser($current_browser);
        $this->postJson('/check-answer', $this->AnswerPayload())->assertOk();
    }

    #[Test]
    public function AllLearningRoutesEnforceTheSessionWithoutChangingOtherRoutes(): void
    {
        $learning_routes = ['home3', 'quiz.saveAnswer', 'quiz.next', 'home.level', 'home.term',
            'home.section', 'quiz.show', 'quiz.submit', 'results.show', 'myresults',
            'leaderboard', 'submit.leaderboard', 'check.answer'];
        foreach (app('router')->getRoutes() as $route) {
            $has_session_check = in_array(EnsureCurrentStudentSession::class, $route->gatherMiddleware(), true);
            $this->assertSame(in_array($route->getName(), $learning_routes, true), $has_session_check, $route->uri());
        }
    }

    #[Test]
    public function SessionValidationReadsTheLatestDatabaseStateAndRejectsDeletedStudents(): void
    {
        $student = $this->CreateStudent();
        $this->LoginStudent();
        $request = Request::create('/quiz-home');
        $request->setLaravelSession(session()->driver());
        $student_sessions = app(StudentSessionService::class);
        $this->assertTrue($student_sessions->IsCurrent($student, $request));
        Contact::whereKey($student->id)->update(['elearning_session_token_hash' => str_repeat('0', 64)]);
        $this->assertFalse($student_sessions->IsCurrent($student, $request));
        $student->delete();
        $this->assertFalse($student_sessions->IsCurrent($student, $request));
    }

    #[Test]
    public function DeletedStudentCannotReceiveANewSessionToken(): void
    {
        $student = $this->CreateStudent();
        $student->delete();
        $request = Request::create('/students-login-check', 'POST');
        $request->setLaravelSession(session()->driver());

        try {
            app(StudentSessionService::class)->Start($student, $request);
            $this->fail('A deleted student must not receive a session token.');
        } catch (ModelNotFoundException) {
            $this->assertFalse(session()->has('elearning_session_token'));
            $this->assertGuest('student');
        }
    }

    #[Test]
    public function MigrationRollbackPreservesStudentRecords(): void
    {
        $student = $this->CreateStudent();
        $migration = require database_path('migrations/2026_11_02_000001_add_elearning_session_token_hash_to_contacts.php');

        $migration->down();
        $this->assertDatabaseHas('contacts', ['id' => $student->id, 'code' => 'BR0001']);
        $migration->up();
        $this->assertNull($student->fresh()->elearning_session_token_hash);
        $this->LoginStudent();
        $this->assertNotNull($student->fresh()->elearning_session_token_hash);
    }

    private function AnswerPayload(): array
    {
        return ['type' => 'written', 'userAnswer' => '4', 'correctAnswer' => '4'];
    }

    private function LoginStudent(): array
    {
        $this->post('/students-login-check', [
            'code' => 'BR0001', 'password' => 'correct-password',
        ])->assertRedirect(route('home3'));

        return session()->all();
    }

    private function RestoreBrowser(array $browser_session): void
    {
        session()->flush();
        session()->put($browser_session);
        $this->app['auth']->forgetGuards();
        Auth::shouldUse('web');
    }

    private function CreateStudent(): Contact
    {
        return Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }
}
