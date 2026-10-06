<?php

namespace Tests\Feature\Branch;

use App\Exports\ExportReciept;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\Receipt;
use App\Models\bookuse;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\LogHistory;
use App\Models\User;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HistoricalReferenceIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch_a;
    private Department $branch_b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch_a = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $this->branch_b = Department::create(['name' => 'Branch B', 'code' => 'BB', 'status' => 1]);
    }

    private function Actor(?Department $branch = null): User
    {
        $actor = User::factory()->create(['department_id' => ($branch ?? $this->branch_a)->id, 'disable' => 0, 'password' => bcrypt('test-only-password')]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        foreach (['history-list', 'history-edit', 'receipt-export'] as $permission_name) {
            $actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $actor;
    }

    private function Student(): Contact
    {
        return Contact::create([
            'centre' => $this->branch_a->id, 'type' => 'student', 'code' => 'AA00001',
            'name' => 'Historical Student', 'telephone' => 'private-student-phone',
            'father_email' => 'private-parent@example.test',
            'level_name' => 'Current level', 'level2_name' => 'Another current level',
        ]);
    }

    private function History(Contact $student, ?User $teacher = null): Histrories
    {
        return Histrories::create([
            'centre' => $this->branch_a->id, 'student_id' => $student->id,
            'teacher_id' => $teacher?->id ?? 0, 'level_id' => 1, 'level_name' => 'Historical level',
            'term' => '1', 'bookuse' => '1', 'course_remaining' => '9',
            'date' => '2026-01-10', 'stime' => '10:00:00', 'etime' => '11:00:00',
            'start_date' => '1999-01-01', 'end_date' => '1999-05-01',
            'comment' => 'Historical lesson', 'signature' => '',
        ]);
    }

    private function RegisterHistoricalCourse(Contact $student): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', static fn (...$arguments) => implode('', $arguments));
        bookuse::forceCreate(['id' => 1, 'name' => 'Historical book', 'qty' => 10, 'level_id' => 1, 'term_id' => 1]);
        bookuse::forceCreate(['id' => 2, 'name' => 'Next book', 'qty' => 10, 'level_id' => 1, 'term_id' => 2]);
        $order = Order::create(['centre' => $this->branch_a->id, 'cid' => $student->id, 'status' => 1]);
        foreach ([1, 2] as $book_id) {
            OrderDetail::create(['order_id' => $order->id, 'product_id' => $book_id, 'quantity' => 1, 'price' => 100]);
        }
        OrderDetailList::create(['order_id' => $order->id, 'level' => 'Historical level', 'term' => '1', 'book' => 'Historical book']);
    }

    private function HistoryUpdate(int $teacher_id): array
    {
        return [
            'teacher_id' => $teacher_id, 'level_id' => 1, 'term' => '1', 'bookuse' => '1',
            'date' => '2026-01-10', 'stime' => '10:00:00', 'etime' => '11:00:00',
            'start_date' => '2026-01-10', 'end_date' => '2026-05-10',
            'comment' => 'Corrected lesson comment', 'signature' => 'data:image/png;base64,dGVzdA==',
        ];
    }

    #[Test]
    public function ReadingHistoryCalculatesDisplayDatesWithoutUpdatingStoredDates(): void
    {
        $actor = $this->Actor();
        $history = $this->History($this->Student());
        $audit_count = DB::table('branch_audit_logs')->count();
        $this->actingAs($actor)->getJson('/histories/'.$history->id)->assertOk()
            ->assertJsonPath('history.start_date', '2026-01-10')
            ->assertJsonPath('history.end_date', '2026-05-10');
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'start_date' => '1999-01-01', 'end_date' => '1999-05-01']);
        $this->assertSame($audit_count, DB::table('branch_audit_logs')->count());
    }

    #[Test]
    #[DataProvider('HistoricalTeacherStates')]
    public function HistoricalLogKeepsMinimalStudentIdentityReadableAfterTransferOrRemoval(string $student_state): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $student->update(['name' => '<script>Historical Student</script>', 'nickname' => '<b>Nickname</b>']);
        $history = $this->History($student);
        LogHistory::create([
            'centre' => $this->branch_a->id, 'history_id' => $history->id, 'student_id' => $student->id,
            'comment_old' => 'Old comment', 'comment_new' => 'Corrected comment',
        ]);
        if ($student_state === 'moved') {
            DB::table('contacts')->where('id', $student->id)->update(['centre' => $this->branch_b->id]);
        } else {
            DB::table('contacts')->where('id', $student->id)->delete();
        }
        $response = $this->actingAs($actor)->getJson('/histories/log/'.$history->id)->assertOk();
        $html = $response->json('html');
        $this->assertStringContainsString('Corrected comment', $html);
        if ($student_state === 'moved') {
            $this->assertStringContainsString('AA00001', $html);
            $this->assertStringContainsString('&lt;script&gt;Historical Student&lt;/script&gt;', $html);
            $this->assertStringContainsString('&lt;b&gt;Nickname&lt;/b&gt;', $html);
            $this->assertStringNotContainsString('<script>Historical Student', $html);
        } else {
            $this->assertStringContainsString('Student not found.', $html);
        }
        $this->assertStringNotContainsString('private-student-phone', $html);
        $this->assertStringNotContainsString('private-parent@example.test', $html);
        $this->actingAs($this->Actor($this->branch_b))->getJson('/histories/log/'.$history->id)->assertForbidden();
    }

    #[Test]
    public function UpdatingATransferredStudentsOldLessonIsRejectedBeforeChangingTheLesson(): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $history = $this->History($student, $actor);
        $this->RegisterHistoricalCourse($student);
        DB::table('contacts')->where('id', $student->id)->update(['centre' => $this->branch_b->id]);
        File::partialMock()->shouldReceive('put')->andReturn(4);
        $this->actingAs($actor)->putJson('/history/update/'.$history->id, $this->HistoryUpdate($actor->id))->assertStatus(409);
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'comment' => 'Historical lesson']);
        $this->assertDatabaseCount('history_log', 0);
    }

    #[Test]
    #[DataProvider('HistoricalTeacherStates')]
    public function HistoricalLessonIsReadOnlyWhenItsStudentIsNoLongerAccessibleInTheBranch(string $student_state): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $history = $this->History($student, $actor);
        $this->RegisterHistoricalCourse($student);
        if ($student_state === 'moved') {
            DB::table('contacts')->where('id', $student->id)->update(['centre' => $this->branch_b->id]);
        } else {
            DB::table('contacts')->where('id', $student->id)->delete();
        }
        $this->actingAs($actor)->getJson('/histories/'.$history->id.'/edit')->assertOk()
            ->assertJsonPath('data.is_read_only', true);
        File::partialMock()->shouldReceive('put')->never();
        $this->putJson('/history/update/'.$history->id, $this->HistoryUpdate($actor->id))->assertStatus(409);
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'comment' => 'Historical lesson', 'start_date' => '1999-01-01']);
        $this->assertDatabaseCount('history_log', 0);
    }

    #[Test]
    #[DataProvider('HistoricalTeacherStates')]
    public function EditingTheOwningLessonCanRetainItsPreviouslyRecordedTeacher(string $teacher_state): void
    {
        $this->withoutExceptionHandling();
        $actor = $this->Actor();
        $teacher = $this->Actor();
        $student = $this->Student();
        $history = $this->History($student, $teacher);
        $this->RegisterHistoricalCourse($student);
        if ($teacher_state === 'moved') {
            DB::table('users')->where('id', $teacher->id)->update(['department_id' => $this->branch_b->id]);
        } else {
            DB::table('users')->where('id', $teacher->id)->delete();
        }
        File::partialMock()->shouldReceive('put')->andReturn(4);
        $this->actingAs($actor)->putJson('/history/update/'.$history->id, $this->HistoryUpdate($teacher->id))->assertOk()
            ->assertJsonPath('success', 'History updated successfully');
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'teacher_id' => $teacher->id, 'comment' => 'Corrected lesson comment']);
        $this->assertDatabaseHas('history_log', ['history_id' => $history->id, 'comment_new' => 'Corrected lesson comment']);
        $this->withExceptionHandling();
        $this->actingAs($this->Actor($this->branch_b))->putJson('/history/update/'.$history->id, $this->HistoryUpdate($teacher->id))->assertForbidden();
        $this->assertDatabaseCount('history_log', 1);
    }

    public static function HistoricalTeacherStates(): array
    {
        return [['moved'], ['deleted']];
    }

    #[Test]
    public function ReceiptExportResolvesTheLevelRecordedOnTheReceiptInsteadOfTheStudentsCurrentLevels(): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        DB::table('levels')->insert(['id' => 42, 'name' => 'Receipt level label']);
        $receipt = Receipt::create([
            'centre' => $this->branch_a->id, 'cid' => $student->id,
            'student_name' => 'Issued student name', 'level' => '42', 'total_fee' => '100.00',
        ]);
        $row = app(BranchContext::class)->runAs($actor, fn () => (new ExportReciept([$receipt->id]))->collection()->first());
        $this->assertSame('Receipt level label', $row['level name']);
    }

    #[Test]
    #[DataProvider('ReceiptStudentStates')]
    public function ReceiptExportUsesIssuedSnapshotWhenTheStudentMovesOrDisappears(string $student_state, ?string $issued_level, string $expected_level): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $receipt = Receipt::create([
            'centre' => $this->branch_a->id, 'cid' => $student->id,
            'receipt_number' => 'AA-202601-0001', 'payment_date' => '2026-01-10',
            'student_no' => 'Issued student code', 'student_name' => 'Issued student name',
            'level' => $issued_level, 'total_fee' => '1500.00',
        ]);
        $foreign_student = Contact::create(['centre' => $this->branch_b->id, 'name' => 'Foreign student', 'type' => 'student']);
        $foreign_receipt = Receipt::create([
            'centre' => $this->branch_b->id, 'cid' => $foreign_student->id,
            'receipt_number' => 'BB-202601-0001', 'student_name' => 'Foreign receipt name',
            'level' => 'Foreign level', 'total_fee' => '9999.00',
        ]);
        if ($student_state === 'moved') {
            DB::table('contacts')->where('id', $student->id)->update(['centre' => $this->branch_b->id]);
        } elseif ($student_state === 'deleted') {
            DB::table('contacts')->where('id', $student->id)->delete();
        }

        $export_rows = app(BranchContext::class)->runAs($actor, fn () =>
            (new ExportReciept([$receipt->id, $foreign_receipt->id]))->collection()->values()->all()
        );
        $this->assertSame([[
            'receipt_number' => 'AA-202601-0001', 'payment_date' => '2026-01-10',
            'code' => 'Issued student code', 'name' => 'Issued student name',
            'level name' => $expected_level, 'total_fee' => 1500,
        ]], $export_rows);
        $this->assertStringNotContainsString('private-student-phone', json_encode($export_rows));
        $this->assertStringNotContainsString('Foreign receipt name', json_encode($export_rows));
    }

    public static function ReceiptStudentStates(): array
    {
        return [
            'Transferred student' => ['moved', 'Issued level', 'Issued level'],
            'Removed student' => ['deleted', 'Issued level', 'Issued level'],
            'No recorded level' => ['present', null, ''],
        ];
    }

    #[Test]
    #[DataProvider('MissingTeacherLocales')]
    public function MissingRecordedTeacherIsDistinguishedFromAnUnrecordedTeacher(string $locale, string $unavailable_message, string $unrecorded_message): void
    {
        $actor = $this->Actor();
        $actor->forceFill(['language_code' => $locale])->save();
        $teacher = $this->Actor();
        $student = $this->Student();
        $recorded_history = $this->History($student, $teacher);
        $unrecorded_history = $this->History($student);
        DB::table('users')->where('id', $teacher->id)->delete();

        $this->actingAs($actor);
        $this->getJson('/histories/'.$recorded_history->id)->assertOk()
            ->assertJsonPath('teacher', $unavailable_message);
        $this->getJson('/histories/'.$recorded_history->id.'/edit')->assertOk()
            ->assertJsonPath('data.teacher', $unavailable_message)
            ->assertJsonPath('data.teacher_id', $teacher->id);
        $this->getJson('/histories/'.$unrecorded_history->id)->assertOk()
            ->assertJsonPath('teacher', $unrecorded_message);
    }

    public static function MissingTeacherLocales(): array
    {
        return [
            'English' => ['en', 'Recorded teacher is unavailable.', 'No teacher recorded'],
            'Thai' => ['th', 'ไม่พบข้อมูลครูผู้สอนที่บันทึกไว้', 'ไม่ระบุครู'],
        ];
    }

    #[Test]
    public function RecordedTeacherRemainsVisibleAfterMovingBranchesWithoutExposingTheirAccount(): void
    {
        $actor = $this->Actor();
        $teacher = $this->Actor();
        $teacher->update(['name' => 'Historical Teacher', 'email' => 'private-teacher@example.test']);
        $history = $this->History($this->Student(), $teacher);
        $foreign_teacher = $this->Actor($this->branch_b);
        DB::table('users')->where('id', $teacher->id)->update(['department_id' => $this->branch_b->id]);

        $this->actingAs($actor);
        $this->getJson('/histories/'.$history->id)->assertOk()->assertJsonPath('teacher', 'Historical Teacher');
        $edit_response = $this->getJson('/histories/'.$history->id.'/edit')->assertOk()
            ->assertJsonPath('data.teacher', 'Historical Teacher')
            ->assertJsonPath('data.teacher_id', $teacher->id);
        $this->assertStringNotContainsString('private-teacher@example.test', $edit_response->getContent());
        $this->assertSame(0, app(BranchContext::class)->runAs($actor, fn () => User::whereKey($teacher->id)->count()));
        $this->putJson('/histories/'.$history->id, ['teacher_id' => $foreign_teacher->id, 'comment' => 'Unauthorized assignment'])
            ->assertForbidden();
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'comment' => 'Historical lesson']);
    }

    #[Test]
    public function MissingStudentDoesNotPreventReadingTheOwningHistoricalLesson(): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $history = $this->History($student);
        DB::table('contacts')->where('id', $student->id)->delete();
        $this->actingAs($actor);
        $this->getJson('/histories/'.$history->id)->assertOk()
            ->assertJsonPath('student', 'Student not found.')
            ->assertJsonPath('history.student_id', $student->id);
        $this->getJson('/histories/'.$history->id.'/edit')->assertOk()
            ->assertJsonPath('data.code', '')
            ->assertJsonPath('data.student_id', 'Student not found.');
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'student_id' => $student->id]);
    }

    #[Test]
    public function OriginalBranchCanReadTransferredStudentHistoryWithoutReadingTheirCurrentProfileOrWritingDates(): void
    {
        $actor = $this->Actor();
        $student = $this->Student();
        $history = $this->History($student);
        DB::table('contacts')->where('id', $student->id)->update(['centre' => $this->branch_b->id]);
        $audit_count = DB::table('branch_audit_logs')->count();

        $this->actingAs($actor);
        foreach (['/histories/'.$history->id, '/histories/'.$history->id.'/edit'] as $path) {
            $response = $this->getJson($path)->assertOk()
                ->assertJsonPath('history.start_date', '2026-01-10')
                ->assertJsonPath('history.end_date', '2026-05-10');
            if (str_ends_with($path, '/edit')) {
                $response->assertJsonPath('data.code', 'AA00001')
                    ->assertJsonPath('data.student_id', 'Historical Student');
            } else {
                $response->assertJsonPath('student', 'AA00001 Historical Student');
            }
            $this->assertStringNotContainsString('private-student-phone', $response->getContent());
            $this->assertStringNotContainsString('private-parent@example.test', $response->getContent());
            $this->assertStringNotContainsString('Current level', $response->getContent());
        }
        $this->assertDatabaseHas('histrories', ['id' => $history->id, 'start_date' => '1999-01-01', 'end_date' => '1999-05-01']);
        $this->assertSame($audit_count, DB::table('branch_audit_logs')->count());
        $this->assertSame(0, app(BranchContext::class)->runAs($actor, fn () => Contact::count()));
        $this->actingAs($this->Actor($this->branch_b))->getJson('/histories/'.$history->id)->assertForbidden();
        $this->getJson('/histories/'.$history->id.'/edit')->assertForbidden();
    }
}
