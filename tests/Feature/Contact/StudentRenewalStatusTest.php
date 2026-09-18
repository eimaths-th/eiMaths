<?php

namespace Tests\Feature\Contact;

use App\Http\Controllers\APIController;
use App\Http\Controllers\HistoriesController;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentRenewalStatusTest extends TestCase
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
            Permission::findOrCreate('history-create', 'web'),
        ]);

        $this->actor = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
        $this->actingAs($this->actor);

        level::forceCreate(['id' => 1, 'name' => 'K1', 'status' => 1]);
        term::forceCreate(['id' => 1, 'level_id' => 1, 'name' => '1', 'status' => 1]);
    }

    #[Test]
    public function StudentListMarksAStudentForRenewalAfterPaidAndFreeLessonsAreUsed(): void
    {
        [$student, $book] = $this->createStudent('ST-EXHAUSTED', 5, 2);
        $this->createHistories($student, $book, 7);

        $row = $this->studentListRows()->firstWhere('id', $student->id);

        $this->assertNotNull($row);
        $this->assertSame('pending', $row['renewal_status']);
        $this->assertSame('course_exhausted', $row['renewal_reason']);
        $this->assertSame(0, $row['renewal_remaining']);
        $this->assertStringContainsString('Pending renewal', $row['remaining']);
        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    #[Test]
    public function StudentListMarksAStudentForRenewalAfterTheCurrentCourseExpires(): void
    {
        [$student, $book] = $this->createStudent('ST-EXPIRED', 5, 2);
        $this->createHistories(
            $student,
            $book,
            1,
            Carbon::today()->subMonths(5)->toDateString(),
            Carbon::yesterday()->toDateString(),
        );

        $row = $this->studentListRows()->firstWhere('id', $student->id);

        $this->assertNotNull($row);
        $this->assertSame('pending', $row['renewal_status']);
        $this->assertSame('course_expired', $row['renewal_reason']);
        $this->assertSame(6, $row['renewal_remaining']);
        $this->assertStringContainsString('Course expired', $row['remaining']);
    }

    #[Test]
    public function StudentListKeepsAnUnexpiredStudentActive(): void
    {
        [$student, $book] = $this->createStudent('ST-ACTIVE', 5, 2);
        $this->createHistories(
            $student,
            $book,
            1,
            Carbon::today()->subMonth()->toDateString(),
            Carbon::tomorrow()->toDateString(),
        );

        $row = $this->studentListRows()->firstWhere('id', $student->id);

        $this->assertNotNull($row);
        $this->assertSame('active', $row['renewal_status']);
        $this->assertNull($row['renewal_reason']);
        $this->assertSame(6, $row['renewal_remaining']);
    }

    #[Test]
    public function StudentListCanFilterStudentsWaitingForRenewal(): void
    {
        [$pending_student, $pending_book] = $this->createStudent('ST-PENDING', 1, 0);
        $this->createHistories($pending_student, $pending_book, 1);
        $this->createStudent('ST-ACTIVE-FILTER', 5, 0);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('contacts') . '?draw=1&start=0&length=10&renewal_status=pending')
            ->assertOk();

        $rows = collect($response->json('data'));

        $this->assertCount(1, $rows);
        $this->assertSame($pending_student->id, $rows->first()['id']);
        $this->assertSame('pending', $rows->first()['renewal_status']);
    }

    #[Test]
    public function StudentListPageIncludesTheCourseStatusFilter(): void
    {
        $this->createStudent('ST-FILTER-UI', 1, 0);

        $this->get(route('contacts'))
            ->assertOk()
            ->assertSee('renewal-status-filter')
            ->assertSee('Pending renewal');
    }

    #[Test]
    public function StudentListOffersANotContinuingShortcutForEditableUsers(): void
    {
        [$student] = $this->createStudent('ST-NOT-CONTINUING-UI', 1, 0);

        $row = $this->studentListRows()->firstWhere('id', $student->id);

        $this->assertNotNull($row);
        $this->assertStringContainsString('btn-discontinue-student', $row['action']);
        $this->assertStringContainsString('Not continuing', $row['action']);
    }

    #[Test]
    public function ThaiStudentListKeepsRenewalCodesAndLocalizesActions(): void
    {
        $this->actor->update(['language_code' => 'th']);
        [$student, $book] = $this->createStudent('ST-TH-RENEWAL', 1, 0);
        $this->createHistories($student, $book, 1);

        $row = $this->studentListRows()->firstWhere('id', $student->id);

        $this->assertNotNull($row);
        $this->assertSame('pending', $row['renewal_status']);
        $this->assertSame('course_exhausted', $row['renewal_reason']);
        $this->assertSame(0, $row['renewal_remaining']);
        $this->assertStringContainsString('รอต่อคลาส', $row['remaining']);
        $this->assertStringContainsString('เช็กชื่อ', $row['action']);
        $this->assertStringContainsString('ไม่ต่อคลาส', $row['action']);
        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    #[Test]
    public function StudentListRendersTheNotContinuingConfirmationModal(): void
    {
        $this->createStudent('ST-NOT-CONTINUING-MODAL', 1, 0);

        $this->get(route('contacts'))
            ->assertOk()
            ->assertSee('discontinue-student-modal')
            ->assertSee('confirm-discontinue-student');
    }

    #[Test]
    public function NotContinuingShortcutMarksTheStudentWithAnAuditDateAndReason(): void
    {
        [$student] = $this->createStudent('ST-NOT-CONTINUING', 1, 0);

        $this->postJson('/contacts/' . $student->id . '/discontinue', [
            'discontinued_date' => '2026-09-18',
            'discontinued_reason' => 'No Time',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.discontinued', 1)
            ->assertJsonPath('data.discontinued_date', '2026-09-18')
            ->assertJsonPath('data.discontinued_reason', 'No Time');

        $this->assertDatabaseHas('contacts', [
            'id' => $student->id,
            'discontinued' => 1,
            'discontinued_date' => '2026-09-18',
            'discontinued_reason' => 'No Time',
        ]);
    }

    #[Test]
    public function NotContinuingShortcutUsesASensibleDefaultReasonWhenNoneIsProvided(): void
    {
        [$student] = $this->createStudent('ST-NOT-CONTINUING-DEFAULT', 1, 0);

        $this->postJson('/contacts/' . $student->id . '/discontinue', [
            'discontinued_date' => '2026-09-18',
        ])->assertOk();

        $this->assertDatabaseHas('contacts', [
            'id' => $student->id,
            'discontinued' => 1,
            'discontinued_reason' => 'Not continuing class',
        ]);
    }

    #[Test]
    public function NotContinuingShortcutRejectsUsersWithoutStudentEditPermission(): void
    {
        [$student] = $this->createStudent('ST-NOT-CONTINUING-FORBIDDEN', 1, 0);
        $list_role = Role::create(['name' => 'StudentListOnly', 'guard_name' => 'web']);
        $list_role->givePermissionTo(Permission::findOrCreate('student-list', 'web'));
        $list_user = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('secret'),
        ]);
        $list_user->assignRole($list_role);

        $this->actingAs($list_user)
            ->postJson('/contacts/' . $student->id . '/discontinue', [
                'discontinued_reason' => 'No Time',
            ])->assertForbidden();

        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    #[Test]
    public function NotContinuingShortcutCannotChangeAStudentFromAnotherBranch(): void
    {
        $other_branch = Department::forceCreate([
            'id' => 3,
            'name' => 'Other Branch',
            'code' => 'OTHER',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        [$student] = $this->createStudent('ST-NOT-CONTINUING-BRANCH', 1, 0);
        $student->update(['centre' => $other_branch->id]);

        $this->postJson('/contacts/' . $student->id . '/discontinue', [
            'discontinued_reason' => 'No Time',
        ])->assertForbidden();

        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    #[Test]
    public function NotContinuingShortcutRejectsNonStudentContacts(): void
    {
        $contact = Contact::forceCreate([
            'centre' => $this->branch->id,
            'type' => 'parent',
            'name' => 'Parent Contact',
            'discontinued' => 0,
        ]);

        $this->postJson('/contacts/' . $contact->id . '/discontinue', [
            'discontinued_reason' => 'No Time',
        ])->assertStatus(422);

        $this->assertSame(0, (int) $contact->fresh()->discontinued);
    }

    #[Test]
    public function WebHistoryCheckInDoesNotAutoDiscontinueAStudentAtTheFinalLesson(): void
    {
        [$student, $book] = $this->createStudent('ST-WEB-FINAL', 2, 0);
        $this->addProductRange($student, $book);
        $this->createHistories($student, $book, 1);

        File::shouldReceive('put')->once()->andReturn(true);
        File::shouldReceive('exists')->andReturnUsing(static fn (string $path): bool => is_file($path));
        File::shouldReceive('getRequire')->andReturnUsing(static fn (string $path): array => require $path);
        $history_controller = \Mockery::mock(HistoriesController::class)->makePartial();
        $history_controller->shouldReceive('reorder_remain')->once()->andReturnNull();
        $this->app->instance(HistoriesController::class, $history_controller);

        $this->postJson(route('histories.store'), [
            'centre' => $this->branch->id,
            'student_id' => $student->id,
            'teacher_id' => $this->actor->id,
            'level_id' => 1,
            'term' => 1,
            'bookuse' => $book->id,
            'date' => Carbon::today()->toDateString(),
            'stime' => '09:00:00',
            'etime' => '10:00:00',
            'comment' => 'Final lesson',
            'signature' => 'data:image/png;base64,' . base64_encode('signature'),
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseCount('histrories', 2);
        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    #[Test]
    public function ApiHistoryCheckInDoesNotAutoDiscontinueAStudentAtTheFinalLesson(): void
    {
        [$student, $book] = $this->createStudent('ST-API-FINAL', 2, 0);
        $this->addProductRange($student, $book);
        $this->createHistories($student, $book, 1);

        $api_controller = \Mockery::mock(APIController::class)->makePartial();
        $api_controller->shouldReceive('reorder_remain')->once()->andReturnNull();
        $this->app->instance(APIController::class, $api_controller);

        $this->withToken($this->actor->createToken('history-test')->plainTextToken)
            ->postJson('/api/savehistories', [
                'centre' => $this->branch->id,
                'student' => $student->id,
                'subject' => 1,
                'term' => 1,
                'book' => $book->id,
                'date' => Carbon::today()->toDateString(),
                'stime' => '09:00:00',
                'etime' => '10:00:00',
                'comments' => 'Final lesson',
            ])->assertOk()->assertJsonPath('message', 'Data Save Successfully');

        $this->assertDatabaseCount('histrories', 2);
        $this->assertSame(0, (int) $student->fresh()->discontinued);
    }

    private function studentListRows(): \Illuminate\Support\Collection
    {
        return collect($this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('contacts') . '?draw=1&start=0&length=10')
            ->assertOk()
            ->json('data'));
    }

    private function createStudent(string $code, int $quantity, int $free_course): array
    {
        $book = bookuse::forceCreate([
            'name' => 'K1 Term 1',
            'type' => 1,
            'qty' => $quantity,
            'level_id' => 1,
            'term_id' => 1,
            'status' => 1,
        ]);

        $student = Contact::forceCreate([
            'centre' => $this->branch->id,
            'type' => 'student',
            'code' => $code,
            'name' => 'Test Student ' . $code,
            'free_course' => $free_course,
            'level' => 1,
            'term' => 1,
            'bookuse' => $book->id,
            'level_name' => 'K1',
            'term_name' => '1',
            'bookuse_name' => $book->name,
            'level2' => 1,
            'term2' => 1,
            'bookuse2' => $book->id,
        ]);

        $order = Order::forceCreate([
            'centre' => $this->branch->id,
            'cid' => $student->id,
            'status' => 1,
        ]);
        OrderDetailList::forceCreate([
            'order_id' => $order->id,
            'level' => 'K1',
            'term' => '1',
            'book' => $book->name,
        ]);

        return [$student, $book];
    }

    private function addProductRange(Contact $student, bookuse $book): void
    {
        $order = Order::where('cid', $student->id)->firstOrFail();
        for ($detail_index = 0; $detail_index < 2; $detail_index++) {
            OrderDetail::forceCreate([
                'order_id' => $order->id,
                'product_id' => $book->id,
                'pname' => $book->name,
                'punit' => 'course',
                'quantity' => 1,
                'price' => 0,
            ]);
        }
    }

    private function createHistories(
        Contact $student,
        bookuse $book,
        int $count,
        ?string $start_date = null,
        ?string $end_date = null,
    ): void {
        for ($history_index = 0; $history_index < $count; $history_index++) {
            Histrories::forceCreate([
                'centre' => $student->centre,
                'student_id' => $student->id,
                'teacher_id' => $this->actor->id,
                'level_id' => 1,
                'level_name' => 'K1',
                'term' => '1',
                'bookuse' => (string) $book->id,
                'course_remaining' => max($book->qty - $history_index - 1, 0),
                'date' => Carbon::today()->subDays($count - $history_index)->toDateString(),
                'stime' => '09:00:00',
                'etime' => '10:00:00',
                'start_date' => $start_date ?? Carbon::today()->subDays($count)->toDateString(),
                'end_date' => $end_date ?? Carbon::today()->addMonths(4)->toDateString(),
                'comment' => '',
                'signature' => '',
            ]);
        }
    }
}
