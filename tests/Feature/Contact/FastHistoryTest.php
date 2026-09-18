<?php

namespace Tests\Feature\Contact;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FastHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Test Branch',
            'code' => 'TEST',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo([
            Permission::findOrCreate('student-list', 'web'),
            Permission::findOrCreate('student-edit', 'web'),
        ]);

        $this->actor = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
        $this->actingAs($this->actor);
    }

    #[Test]
    public function PreviewSelectsTheLatestIncompleteRegisteredCourse(): void
    {
        [$student, $first_book, $second_book] = $this->createStudentWithTwoCourses();
        $this->createHistories($student, 1, '1', 10, $first_book->id);
        $this->createHistories($student, 1, '2', 1, $second_book->id);

        $response = $this->getJson(route('contacts.fastHistory.preview', $student->id));
        $response->assertOk()
            ->assertJsonPath('data.level_id', 1)
            ->assertJsonPath('data.term', '2')
            ->assertJsonPath('data.bookuse_id', $second_book->id)
            ->assertJsonPath('data.attended_count', 1)
            ->assertJsonPath('data.remaining_after', 8)
            ->assertJsonPath('data.registered_courses.1.term', '2')
            ->assertJsonPath('data.registered_courses.1.bookuses.0.id', $second_book->id)
            ->assertJsonPath('data.teacher_options.0.id', $this->actor->id);
    }

    #[Test]
    public function PreviewDoesNotCreateAHistoryAndPostCreatesOneWithTheResolvedCourse(): void
    {
        [$student, $first_book] = $this->createStudentWithTwoCourses();

        $this->getJson(route('contacts.fastHistory.preview', $student->id))
            ->assertOk()
            ->assertJsonPath('data.level_id', 1)
            ->assertJsonPath('data.term', '1');

        $this->assertDatabaseCount('histrories', 0);

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.level_id', 1)
            ->assertJsonPath('data.term', '1')
            ->assertJsonPath('data.bookuse', (string) $first_book->id)
            ->assertJsonPath('data.course_remaining', '9');

        $this->assertDatabaseCount('histrories', 1);
    }

    #[Test]
    public function PostPersistsTheCourseTeacherDateAndTwentyFourHourTimesSelectedInTheShortcut(): void
    {
        [$student, $first_book, $second_book] = $this->createStudentWithTwoCourses();
        $teacher = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('secret'),
        ]);
        $teacher->assignRole(Role::findOrCreate('Teacher', 'web'));
        $selected_date = '2026-09-17';

        $this->postJson(route('contacts.fastHistory', $student->id), [
            'level_id' => 1,
            'term' => '2',
            'bookuse' => $second_book->id,
            'teacher_id' => $teacher->id,
            'date' => $selected_date,
            'stime' => '13:30:00',
            'etime' => '15:00:00',
        ])->assertOk()
            ->assertJsonPath('data.level_id', 1)
            ->assertJsonPath('data.term', '2')
            ->assertJsonPath('data.bookuse', (string) $second_book->id)
            ->assertJsonPath('data.teacher_id', $teacher->id)
            ->assertJsonPath('data.date', $selected_date)
            ->assertJsonPath('data.stime', '13:30:00')
            ->assertJsonPath('data.etime', '15:00:00');
    }

    #[Test]
    public function ThaiCheckInRecordsOneHistoryAndTranslatesItsDuplicateError(): void
    {
        $this->actor->update(['language_code' => 'th']);
        [$student] = $this->createStudentWithTwoCourses();

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'บันทึกประวัติการเรียนเรียบร้อยแล้ว');

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'นักเรียนเช็กชื่อคอร์สนี้ในวันนี้แล้ว');

        $this->assertDatabaseCount('histrories', 1);
    }

    #[Test]
    public function PostRejectsAnEndTimeThatIsNotAfterTheStartTime(): void
    {
        [$student, $first_book] = $this->createStudentWithTwoCourses();

        $this->postJson(route('contacts.fastHistory', $student->id), [
            'level_id' => 1,
            'term' => '1',
            'bookuse' => $first_book->id,
            'teacher_id' => $this->actor->id,
            'date' => '2026-09-17',
            'stime' => '15:00:00',
            'etime' => '14:00:00',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'End time must be after start time.');

        $this->assertDatabaseCount('histrories', 0);
    }

    #[Test]
    public function PostRejectsABookThatDoesNotBelongToTheSelectedCourse(): void
    {
        [$student, $first_book, $second_book] = $this->createStudentWithTwoCourses();

        $this->postJson(route('contacts.fastHistory', $student->id), [
            'level_id' => 1,
            'term' => '2',
            'bookuse' => $first_book->id,
            'teacher_id' => $this->actor->id,
            'date' => '2026-09-17',
            'stime' => '13:30:00',
            'etime' => '15:00:00',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Selected book does not belong to the selected course.');

        $this->assertDatabaseCount('histrories', 0);
    }

    #[Test]
    public function PostRejectsASecondCheckInForTheSameStudentAndCourseOnTheSameDate(): void
    {
        [$student, $first_book] = $this->createStudentWithTwoCourses();
        $this->createHistories($student, 1, '1', 1, $first_book->id, now()->toDateString());

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('histrories', 1);
    }

    #[Test]
    public function UsersWithOnlyStudentListPermissionCannotCreateFastHistory(): void
    {
        [$student] = $this->createStudentWithTwoCourses();
        $this->actor->syncRoles([]);
        $this->actor->givePermissionTo(Permission::findOrCreate('student-list', 'web'));

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertForbidden();

        $this->assertDatabaseCount('histrories', 0);
    }

    #[Test]
    public function FastHistoryCannotAccessAStudentFromAnotherBranch(): void
    {
        [$student, $first_book] = $this->createStudentWithTwoCourses();
        $other_branch = Department::withoutEvents(fn () => Department::forceCreate([
            'id' => 3,
            'name' => 'Other Branch',
            'code' => 'OTHER',
            'status' => 1,
            'enabled_modules' => null,
        ]));
        $other_student = Contact::withoutEvents(fn () => Contact::forceCreate([
            'centre' => $other_branch->id,
            'type' => 'student',
            'code' => 'ST-OTHER',
            'name' => 'Other Student',
            'level' => $student->level,
            'term' => 1,
            'bookuse' => $first_book->id,
            'level2' => $student->level,
            'term2' => 1,
            'bookuse2' => $first_book->id,
        ]));

        $this->getJson(route('contacts.fastHistory.preview', $other_student->id))
            ->assertForbidden();
        $this->postJson(route('contacts.fastHistory', $other_student->id))
            ->assertForbidden();

        $this->assertDatabaseCount('histrories', 0);
    }

    #[Test]
    public function PostRejectsStudentsWhoHaveCompletedEveryRegisteredCourse(): void
    {
        [$student, $first_book, $second_book] = $this->createStudentWithTwoCourses();
        $this->createHistories($student, 1, '1', 10, $first_book->id);
        $this->createHistories($student, 1, '2', 10, $second_book->id);

        $this->postJson(route('contacts.fastHistory', $student->id))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('histrories', 20);
    }

    #[Test]
    public function StudentListShowsTheCheckInActionToUsersWhoCanEditStudents(): void
    {
        $this->createStudentWithTwoCourses();

        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('contacts').'?draw=1&start=0&length=10')
            ->assertOk()
            ->assertSee('btn-fast-history')
            ->assertSee('Check in');
    }

    #[Test]
    public function StudentListPageIncludesTheCheckInConfirmationModal(): void
    {
        $this->createStudentWithTwoCourses();

        $response = $this->get(route('contacts'));
        $response
            ->assertOk()
            ->assertSee('fast-history-modal')
            ->assertSee('confirm-fast-history')
            ->assertSee('fast-history-level')
            ->assertSee('fast-history-teacher')
            ->assertSee('fast-history-stime')
            ->assertSee('fast-history-etime');

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $dismiss_controls = $xpath->query(
            "//*[@id='fast-history-modal']//*[@data-bs-dismiss='modal']"
        );

        $this->assertSame(2, $dismiss_controls->length);
    }

    private function createStudentWithTwoCourses(): array
    {
        $first_level = level::forceCreate(['id' => 1, 'name' => 'K1', 'status' => 1]);
        $second_level = $first_level;
        term::forceCreate(['id' => 1, 'level_id' => $first_level->id, 'name' => '1', 'status' => 1]);
        term::forceCreate(['id' => 2, 'level_id' => $first_level->id, 'name' => '2', 'status' => 1]);

        $first_book = bookuse::forceCreate([
            'id' => 1,
            'name' => 'K1 Term 1',
            'type' => 1,
            'qty' => 10,
            'level_id' => $first_level->id,
            'term_id' => 1,
            'status' => 1,
        ]);
        $second_book = bookuse::forceCreate([
            'id' => 2,
            'name' => 'K2 Term 1',
            'type' => 1,
            'qty' => 10,
            'level_id' => $first_level->id,
            'term_id' => 2,
            'status' => 1,
        ]);

        $student = Contact::forceCreate([
            'centre' => $this->branch->id,
            'type' => 'student',
            'code' => 'ST-001',
            'name' => 'Test Student',
            'level' => $first_level->id,
            'term' => 1,
            'bookuse' => $first_book->id,
            'level_name' => $first_level->name,
            'term_name' => '1',
            'bookuse_name' => $first_book->name,
            'level2' => $second_level->id,
            'term2' => 2,
            'bookuse2' => $second_book->id,
            'level2_name' => $second_level->name,
            'term2_name' => '2',
            'bookuse2_name' => $second_book->name,
        ]);

        $order = Order::forceCreate(['centre' => $this->branch->id, 'cid' => $student->id, 'status' => 1]);
        OrderDetailList::forceCreate([
            'order_id' => $order->id,
            'level' => $first_level->name,
            'term' => '1',
            'book' => $first_book->name,
        ]);
        OrderDetailList::forceCreate([
            'order_id' => $order->id,
            'level' => $first_level->name,
            'term' => '2',
            'book' => $second_book->name,
        ]);

        return [$student, $first_book, $second_book];
    }

    private function createHistories(Contact $student, int $level_id, string $term_name, int $count, int $bookuse_id, ?string $date = null): void
    {
        for ($history_index = 0; $history_index < $count; $history_index++) {
            Histrories::forceCreate([
                'centre' => $student->centre,
                'student_id' => $student->id,
                'teacher_id' => $this->actor->id,
                'level_id' => $level_id,
                'level_name' => level::find($level_id)->name,
                'term' => $term_name,
                'bookuse' => (string) $bookuse_id,
                'course_remaining' => max(9 - $history_index, 0),
                'date' => $date ?? now()->subDays($count - $history_index)->toDateString(),
                'stime' => '09:00:00',
                'etime' => '10:00:00',
                'start_date' => now()->subDays($count)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'comment' => '',
                'signature' => '',
            ]);
        }
    }
}
