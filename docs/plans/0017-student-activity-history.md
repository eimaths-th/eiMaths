# Student Activity History Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `executing-plans` with the selected method for task-by-task execution.

- **Goal:** Authorized staff open a student and see login history and quiz attempts. Login history is deleted after 365 days.
- **Architecture:** A new `student_login_events` table is written by a focused recorder. A staff page reads it together with existing `tests` rows. Branch isolation comes from the existing `Contact` and `Test` scopes.
- **Tech Stack:** Laravel 10, PHP 8.3, Blade, spatie/laravel-permission, PHPUnit 10, SQLite in-memory for tests.
- **Source:** `docs/specs/0005-student-activity-history-design.md`

## Global Constraints

- Student login and logout must succeed when an event cannot be written.
- Store only the student identifier, event type, and time. Store no IP address, device, or failed login.
- Quiz attempts are read from `tests` and never copied.
- Logs contain identifiers only, never names or contact details.
- Run tests from `eimaths_backend`. `phpunit.xml` forces SQLite `:memory:`.

## Review Focus

- A staff member from another branch is logged in on the same browser when a student logs in. `EnforceBranchContext` sets a staff actor, so a model write could be refused. The login event must still be recorded (Task 2).
- A staff user and a student share the same numeric ID. Only the student's attempts may appear (Task 4).
- A student from another branch must return 404, and must not reveal a login row (Task 3).
- An event exactly 365 days old is kept, and one second older is deleted (Task 6).
- Deleting a student deletes that student's events (Task 1).

## Task 1: Store login events

**Files:**

- Create: `database/migrations/2026_11_07_000001_create_student_login_events_table.php`
- Create: `app/Models/StudentLoginEvent.php`
- Modify: `config/branch.php` at `models`
- Test: `tests/Feature/Contact/StudentLoginEventTest.php`

- **Consumes:** `contacts.id`, which is an unsigned big integer.
- **Produces:** Table `student_login_events`; `App\Models\StudentLoginEvent` with `public const LOGIN = 'LOGIN'` and `public const LOGOUT = 'LOGOUT'`.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the failing test:

```php
<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    private function CreateStudent(): Contact
    {
        return Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }
}
```

- [ ] Run `php vendor/bin/phpunit --filter StudentLoginEventTest`; expect failure because table `student_login_events` does not exist.
- [ ] Create the migration with `id`, `foreignId('contact_id')->constrained('contacts')->cascadeOnDelete()`, `string('event_type', 16)`, `timestamp('occurred_at')`, and indexes on (`contact_id`, `occurred_at`) and `occurred_at`. `down()` drops the table.
- [ ] Create `StudentLoginEvent` with `$timestamps = false`, `$fillable = ['contact_id', 'event_type', 'occurred_at']`, `$casts = ['occurred_at' => 'datetime']`, and the two constants.
- [ ] Add `App\Models\StudentLoginEvent::class => ['parent' => App\Models\Contact::class, 'key' => 'contact_id'],` to `models` in `config/branch.php`.
- [ ] Run `php vendor/bin/phpunit --filter StudentLoginEventTest`; expect exit code 0 and 1 passing test.

## Task 2: Record login and logout

**Files:**

- Create: `app/Domain/Elearning/StudentLoginEventRecorder.php`
- Modify: `app/Http/Controllers/ContactController.php` at `studentsCheckLogin` and `studentsLogout`
- Test: `tests/Feature/Contact/StudentLoginEventTest.php`

- **Consumes:** `StudentLoginEvent` constants from Task 1.
- **Produces:** `StudentLoginEventRecorder::RecordLogin(Contact $student): void` and `RecordLogout(Contact $student): void`.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add these tests to `StudentLoginEventTest`, importing `App\Models\User`, `Illuminate\Support\Facades\Log`, `Illuminate\Support\Facades\Schema`:

```php
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
public function LoginIsRecordedWhileStaffFromAnotherBranchIsSignedInOnTheSameBrowser(): void
{
    Department::forceCreate(['id' => 3, 'name' => 'Other', 'code' => 'OT', 'status' => 1]);
    $staff = User::factory()->create(['department_id' => 3, 'disable' => 0, 'password' => bcrypt('secret')]);
    $student = $this->CreateStudent();

    $this->actingAs($staff)
        ->post('/students-login-check', ['code' => 'BR0001', 'password' => 'correct-password']);

    $this->assertDatabaseHas('student_login_events', ['contact_id' => $student->id, 'event_type' => 'LOGIN']);
}
```

- [ ] Run `php vendor/bin/phpunit --filter StudentLoginEventTest`; expect the new tests that assert events to fail because no event is written.
- [ ] Create `StudentLoginEventRecorder`. Both public methods call a private `Record(Contact $student, string $event_type): void`, which writes with `DB::table('student_login_events')->insert(['contact_id' => $student->getKey(), 'event_type' => $event_type, 'occurred_at' => now()])` inside `try`. Use `DB::table` so the branch `saving` hook cannot refuse the write. `catch (\Throwable)` calls `Log::warning('STUDENT_LOGIN_EVENT_FAILED', ['contact_id' => $student->getKey(), 'event_type' => $event_type])` and returns.
- [ ] Add a `StudentLoginEventRecorder $login_events` parameter to `studentsCheckLogin` and call `$login_events->RecordLogin($student)` directly after `$student = Auth::guard('student')->user();`.
- [ ] Add the same parameter to `studentsLogout` and call `$login_events->RecordLogout($student)` as the first statement inside `if ($student)`.
- [ ] Run `php vendor/bin/phpunit --filter "StudentLoginEventTest|StudentElearningSessionTest|StudentLoginRecaptchaTest"`; expect exit code 0.

## Task 3: Show login history with access control

**Files:**

- Create: `database/migrations/2026_11_07_000002_register_student_activity_permission.php`
- Create: `app/Http/Controllers/StudentActivityController.php`
- Create: `resources/views/contacts/activity.blade.php`
- Modify: `routes/web.php` at the `/contacts/{contact}/book-receipts` group of routes
- Modify: `config/branch-role-templates.php` at `$branch`
- Modify: `config/permission-ui.php` at `labels`
- Modify: `lang/en/user-access.php`, `lang/th/user-access.php` at `permission_labels`
- Modify: `lang/en/students.php`, `lang/th/students.php` at `actions` and a new `activity` group
- Test: `tests/Feature/Contact/StudentActivityPageTest.php`

- **Consumes:** `StudentLoginEvent`; `Tests\Support\QuizResultFixtures`.
- **Produces:** Route `contacts.activity.index` at `GET /contacts/{contact}/activity`; `StudentActivityController::Index(Contact $contact): View`; permission `student-activity-list`.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the failing tests:

```php
<?php

namespace Tests\Feature\Contact;

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
    use QuizResultFixtures;
    use RefreshDatabase;

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
}
```

- [ ] Run `php vendor/bin/phpunit --filter StudentActivityPageTest`; expect failure because route `contacts.activity.index` is not defined.
- [ ] Add inside the `auth` group of `routes/web.php`, directly before the book-receipts routes: `Route::get('/contacts/{contact}/activity', [App\Http\Controllers\StudentActivityController::class, 'Index'])->name('contacts.activity.index');`
- [ ] Create `StudentActivityController` with `__construct()` calling `$this->middleware('permission:student-activity-list')`. `Index(Contact $contact): View` loads `StudentLoginEvent::query()->where('contact_id', $contact->id)->orderByDesc('occurred_at')->orderByDesc('id')->paginate(20, ['*'], 'login_page')->withQueryString()` and returns view `contacts.activity` with `student` and `login_events`.
- [ ] Create the view extending `layouts.app`. Show the heading `{{ __('students.activity.title') }}: {{ $student->nickname ?? $student->name }}` with the student code, a login table with `occurred_at->format('d/m/Y H:i')` and a translated event label, an empty state using `students.activity.no_logins`, and `$login_events->links()`.
- [ ] Add to `students` language files. `en`: `actions.view_activity` = `Student activity`; `activity` = `title` `Student activity`, `logins_heading` `Logins`, `quizzes_heading` `Quizzes`, `event_login` `Login`, `event_logout` `Logout`, `no_logins` `No login history yet.`, `no_quizzes` `No quiz attempts yet.`. `th`: `actions.view_activity` = `ประวัติการใช้งาน`; `activity` = `title` `ประวัติการใช้งาน`, `logins_heading` `การเข้าสู่ระบบ`, `quizzes_heading` `การสอบ`, `event_login` `เข้าสู่ระบบ`, `event_logout` `ออกจากระบบ`, `no_logins` `ยังไม่มีประวัติการเข้าสู่ระบบ`, `no_quizzes` `ยังไม่มีประวัติการสอบ`.
- [ ] Create the permission migration following `2026_11_05_000001_register_quiz_result_permissions.php`. `up()` calls `Permission::findOrCreate('student-activity-list', 'web')`, gives it to the `web` roles named `Admin` and `BranchAdmin` when they exist, and forgets cached permissions. `down()` deletes the permission and forgets cached permissions.
- [ ] Add `'student-activity-list'` to the second `array_merge` list of `$branch` in `config/branch-role-templates.php`.
- [ ] Add the label to `config/permission-ui.php` `labels` as `'student-activity-list' => 'ดูประวัติการใช้งานของนักเรียน'`, to `lang/th/user-access.php` `permission_labels` with the same text, and to `lang/en/user-access.php` `permission_labels` as `'View student activity'`.
- [ ] Add a test to `StudentActivityPageTest` that creates roles `Admin`, `BranchAdmin`, and `Teacher`, runs `(require database_path('migrations/2026_11_07_000002_register_student_activity_permission.php'))->up()`, and asserts the first two have the permission and `Teacher` does not. Add a second assertion after `->down()` that `Permission::where('name', 'student-activity-list')->exists()` is false.
- [ ] Run `php vendor/bin/phpunit --filter "StudentActivityPageTest|UiLocalizedPagesTest|UserAccessLocalizedPageTest"`; expect `StudentActivityPageTest` to pass. Any failure not present in the baseline comparison must be fixed.

## Task 4: Show quiz attempts

**Files:**

- Modify: `app/Http/Controllers/StudentActivityController.php` at `Index`
- Modify: `resources/views/contacts/activity.blade.php`
- Test: `tests/Feature/Contact/StudentActivityPageTest.php`

- **Consumes:** `App\Models\Test`, `quiz-management.preview.view_answers`, route `backoffice.quiz-results.show`.
- **Produces:** View variables `quiz_attempts` (a paginator, or `null` when the section is hidden) and `can_view_answers`.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add these tests:

```php
#[Test]
public function QuizSectionShowsScoreAndAnswerLinkForPermittedStaff(): void
{
    $student = $this->CreateResultStudent(501, 2);
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
    $student = $this->CreateResultStudent(501, 2);
    $this->CreateResultAttempt('student', $student->id);

    $this->actingAs($this->CreateResultActor(['student-activity-list'], 2))
        ->get(route('contacts.activity.index', $student->id))
        ->assertOk()
        ->assertDontSee('Addition practice');
}

#[Test]
public function AnswerLinkIsHiddenWithoutQuizResultsView(): void
{
    $student = $this->CreateResultStudent(501, 2);
    $attempt = $this->CreateResultAttempt('student', $student->id);

    $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list'], 2))
        ->get(route('contacts.activity.index', $student->id))
        ->assertSee('Addition practice')
        ->assertDontSee(route('backoffice.quiz-results.show', $attempt), false);
}

#[Test]
public function QuizSectionExcludesStaffAttemptsWithTheSameId(): void
{
    $student = $this->CreateResultStudent(501, 2);
    User::factory()->create(['id' => 501, 'department_id' => 2, 'disable' => 0, 'password' => 'result-test-password']);
    $this->CreateResultAttempt('user', 501);

    $this->actingAs($this->CreateResultActor(['student-activity-list', 'quiz-results-list'], 2))
        ->get(route('contacts.activity.index', $student->id))
        ->assertOk()
        ->assertDontSee('Addition practice');
}
```

- [ ] Run `php vendor/bin/phpunit --filter StudentActivityPageTest`; expect `QuizSectionShowsScoreAndAnswerLinkForPermittedStaff` and `AnswerLinkIsHiddenWithoutQuizResultsView` to fail because the section does not exist.
- [ ] In `Index`, add `Request $request` and set `quiz_attempts` to `null` unless `$request->user()->can('quiz-results-list')`. Otherwise use `Test::query()->where('type_user', 'student')->where('user_id', $contact->id)->with('quiz:id,title')->withCount('questions')->latest()->paginate(20, ['*'], 'quiz_page')->withQueryString()`. Pass `can_view_answers` as `$request->user()->can('quiz-results-view')`.
- [ ] In the view, when `quiz_attempts` is not `null`, show a table with `created_at->format('d/m/Y H:i')`, the quiz title or `quiz-management.preview.not_available`, `{{ $attempt->result ?? 0 }}/{{ $attempt->questions_count }}`, duration from `time_spent` in seconds as `sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60)`, and the answer link when `can_view_answers`. Use `students.activity.no_quizzes` for the empty state.
- [ ] Run `php vendor/bin/phpunit --filter StudentActivityPageTest`; expect exit code 0.

## Task 5: Add the student list button

**Files:**

- Modify: `app/Http/Controllers/ContactController.php` at the `action` column of `index`
- Test: `tests/Feature/Contact/StudentActivityPageTest.php`

- **Consumes:** Route `contacts.activity.index`; `students.actions.view_activity`.
- **Produces:** A link in the `action` column for permitted staff.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add two tests that create a student in branch 2 and call `$this->actingAs($actor)->getJson('/contacts?draw=1&start=0&length=10', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()`. An actor with `student-list` and `student-activity-list` must have `route('contacts.activity.index', $student->id)` in `data.0.action`. An actor with only `student-list` must not.
- [ ] Run `php vendor/bin/phpunit --filter StudentActivityPageTest`; expect the first new test to fail because the link is missing. If the request fails instead, correct the test setup before continuing.
- [ ] Directly before `return $html;` in the `action` column at `ContactController::index`, add: when `Gate::allows('student-activity-list')`, append `<a class="btn btn-sm btn-secondary" href="…">` using `route('contacts.activity.index', $row->id)`, with `<i class="fa fa-history"></i>` and `e(__('students.actions.view_activity'))`.
- [ ] Run `php vendor/bin/phpunit --filter "StudentActivityPageTest|DataTableOrderingTest"`; expect exit code 0.

## Task 6: Delete old login events

**Files:**

- Create: `config/student-activity.php`
- Create: `app/Console/Commands/PruneStudentLoginEvents.php`
- Modify: `app/Console/Kernel.php` at `schedule`
- Test: `tests/Feature/Contact/StudentLoginEventPruneTest.php`

- **Consumes:** `student_login_events`.
- **Produces:** Command `student-activity:prune-login-events`; config `student-activity.login_retention_days`.
- **Checkpoint:** None.

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the failing tests. Use `RefreshDatabase`, create department 2 and one `Contact` as in Task 1, and freeze time with `Carbon::setTestNow('2026-11-07 03:00:00')`, resetting it in `tearDown`.

```php
#[Test]
public function DeletesOnlyEventsOlderThanTheRetentionPeriod(): void
{
    $kept_at_boundary = $this->CreateEvent(now()->subDays(365));
    $kept_recent = $this->CreateEvent(now()->subDay());
    $this->CreateEvent(now()->subDays(365)->subSecond());

    $this->artisan('student-activity:prune-login-events')->assertExitCode(0);

    $this->assertEqualsCanonicalizing(
        [$kept_at_boundary, $kept_recent],
        DB::table('student_login_events')->pluck('id')->all()
    );
}

#[Test]
public function DeletesEveryExpiredEventAcrossBatches(): void
{
    foreach (range(1, 1001) as $unused_index) {
        $this->CreateEvent(now()->subDays(400));
    }

    $this->artisan('student-activity:prune-login-events')->assertExitCode(0);

    $this->assertDatabaseCount('student_login_events', 0);
}

#[Test]
public function RunsDailyAtBangkokTime(): void
{
    $events = collect(app(Schedule::class)->events())->filter(
        fn ($event): bool => str_contains((string) $event->command, 'student-activity:prune-login-events'));

    $this->assertCount(1, $events);
    $this->assertSame('45 2 * * *', $events->first()->expression);
    $this->assertSame('Asia/Bangkok', $events->first()->timezone);
}

#[Test]
public function ReportsFailureWithoutPersonalData(): void
{
    Schema::drop('student_login_events');
    Log::spy();

    $this->artisan('student-activity:prune-login-events')->assertExitCode(1);

    Log::shouldHaveReceived('error')->once()->with('student_activity.login_events_prune_failed', [
        'error_code' => 'STUDENT_LOGIN_EVENT_PRUNE_FAILED',
    ]);
}
```

`CreateEvent(Carbon $occurred_at): int` calls `DB::table('student_login_events')->insertGetId([...])` with event type `LOGIN`.

- [ ] Run `php vendor/bin/phpunit --filter StudentLoginEventPruneTest`; expect failure because the command does not exist.
- [ ] Create `config/student-activity.php` returning `['login_retention_days' => 365]`.
- [ ] Create `PruneStudentLoginEvents` with signature `student-activity:prune-login-events` and `private const BATCH_SIZE = 1000`. `handle(): int` sets the cutoff to `now()->subDays(config('student-activity.login_retention_days'))`, then in `try` repeats: read up to `BATCH_SIZE` IDs where `occurred_at < cutoff`, stop when none remain, delete those IDs, and add to the count. On success call `Log::info('student_activity.login_events_pruned', ['deleted_count' => $deleted_count])` and return `self::SUCCESS`. `catch (\Throwable)` logs the error shown in the test and returns `self::FAILURE`.
- [ ] Add to `Kernel::schedule`: `$schedule->command('student-activity:prune-login-events')->dailyAt('02:45')->timezone('Asia/Bangkok')->withoutOverlapping();`
- [ ] Run `php vendor/bin/phpunit --filter StudentLoginEventPruneTest`; expect exit code 0.

## Task 7: Verify the whole change

**Files:**

- Test: full suite

- **Consumes:** All earlier tasks.
- **Produces:** A test report that lists every failure.
- **Checkpoint:** Stop and report any failure that is not in the baseline taken before Task 1.

**Actions:**

**For nonbehavioral artifacts:**

- [ ] Run `php -d memory_limit=-1 vendor/bin/phpunit --log-junit <scratchpad>/student-activity.xml`; expect only failures already present in the baseline.
- [ ] Inspect the diff; expect changes only in the files listed in this plan.
- [ ] Report the two release conditions from the specification: the privacy owner's confirmation and a running production scheduler.
