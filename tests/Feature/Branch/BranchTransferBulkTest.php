<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\Order;
use App\Models\Position;
use App\Models\User;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchTransferBulkTest extends TestCase
{
    use RefreshDatabase;

    private Department $source_branch;

    private Department $destination_branch;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'HQ',
            'code' => 'HQ',
            'status' => 1,
            'branch_type' => 'hq',
        ]);
        $this->source_branch = Department::create([
            'name' => 'Source',
            'code' => 'SRC',
            'status' => 1,
            'branch_type' => 'branch',
        ]);
        $this->destination_branch = Department::create([
            'name' => 'Destination',
            'code' => 'DST',
            'status' => 1,
            'branch_type' => 'branch',
        ]);

        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => bcrypt('password-123'),
        ]);
        $role = Role::findOrCreate('SystemAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
        $this->actor->assignRole($role);
    }

    private function Student(string $name = 'Student', ?int $branch_id = null): Contact
    {
        return Contact::create([
            'name' => $name,
            'centre' => $branch_id ?? $this->source_branch->id,
        ]);
    }

    private function Payload(array $records, string $type = 'student'): array
    {
        $sources = [];
        foreach ($records as $record) {
            $sources[$record->id] = (int) ($type === 'user' ? $record->department_id : $record->centre);
        }

        return [
            'type' => $type,
            'record_ids' => array_map(fn ($record) => $record->id, $records),
            'expected_from_branch_ids' => $sources,
            'to_branch_id' => $this->destination_branch->id,
            'reason' => 'Moving to another branch',
        ];
    }

    #[Test]
    public function SourceOnlySearchPaginatesWithoutLeakingOtherBranches(): void
    {
        for ($student_index = 1; $student_index <= 31; $student_index++) {
            $this->Student(sprintf('Student %02d', $student_index));
        }
        $this->Student('Foreign Student', $this->destination_branch->id);

        $url = '/branch-transfers/targets?paginated=1&type=student&from_branch_id='.$this->source_branch->id;
        $this->actingAs($this->actor)->getJson($url)
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('meta.total', 31)
            ->assertJsonPath('data.0.name', 'Student 01');

        $this->getJson($url.'&page=2')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.name', 'Student 26');
    }

    #[Test]
    public function CodeSearchStaysInsideTheSourceAndLegacyShapeIsPreserved(): void
    {
        $local = $this->Student('Local Student');
        $local->update(['code' => 'MATCH-01']);
        $foreign = $this->Student('Foreign Student', $this->destination_branch->id);
        $foreign->update(['code' => 'MATCH-02']);

        $url = '/branch-transfers/targets?paginated=1&type=student&q=MATCH&from_branch_id='.$this->source_branch->id;
        $this->actingAs($this->actor)->getJson($url)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $local->id);

        $this->getJson('/branch-transfers/targets?type=student&q=Local')
            ->assertExactJson([
                'data' => [[
                    'id' => $local->id,
                    'label' => 'Local Student (MATCH-01) — Source',
                ]],
            ]);
        $this->getJson('/branch-transfers/targets?type=student')->assertUnprocessable();
    }

    #[Test]
    public function StudentsMoveTogetherAndKeepIssuedOrdersAtTheSource(): void
    {
        $students = [$this->Student('First'), $this->Student('Second')];
        $orders = [];
        foreach ($students as $student) {
            $order = Order::create([
                'cid' => $student->id,
                'centre' => $this->source_branch->id,
                'status' => 2,
            ]);
            $student->update(['order' => $order->id]);
            $orders[] = $order;
        }

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
            ->assertOk()
            ->assertJsonPath('transferred_count', 2)
            ->assertJsonCount(2, 'data');

        foreach ($students as $student) {
            $this->assertSame($this->destination_branch->id, (int) $student->fresh()->centre);
            $this->assertSame(0, (int) $student->fresh()->order);
        }
        foreach ($orders as $order) {
            $this->assertSame($this->source_branch->id, (int) $order->fresh()->centre);
        }
        $this->assertDatabaseCount('branch_transfers', 2);
    }

    public static function PendingOrderTransferRequests(): array
    {
        return [
            'batch-zero-status' => [true, 0],
            'batch-pending-status' => [true, 1],
            'single-zero-status' => [false, 0],
            'single-pending-status' => [false, 1],
        ];
    }

    #[Test]
    #[DataProvider('PendingOrderTransferRequests')]
    public function StudentWithPendingOrderCanTransferWithoutChangingTheOrder(bool $is_batch, int $order_status): void
    {
        $student = $this->Student();
        $order = Order::create([
            'cid' => $student->id,
            'centre' => $this->source_branch->id,
            'status' => $order_status,
            'total_price' => 1200,
        ])->refresh();
        $student->forceFill([
            'order' => $order->id,
            'new_student_order_id' => $order->id,
            'courses_pending_id' => 77,
        ])->save();
        $this->actor->update(['language_code' => 'th']);

        $payload = $is_batch ? $this->Payload([$student]) : [
            'type' => 'student',
            'record_id' => $student->id,
            'to_branch_id' => $this->destination_branch->id,
            'reason' => 'Moving to another branch',
        ];
        $response = $this->actingAs($this->actor)->postJson('/branch-transfers', $payload);
        $this->assertSame(200, $response->status(), $response->json('record_errors.0.message') ?? $response->json('message') ?? '');
        $response->assertJsonPath('success', true);
        if ($is_batch) {
            $response->assertJsonPath('transferred_count', 1)->assertJsonPath('data.0.id', $student->id);
        } else {
            $response->assertJsonPath('data.id', $student->id);
        }

        $transferred_student = $student->fresh();
        $this->assertSame($this->destination_branch->id, (int) $transferred_student->centre);
        $this->assertSame(0, (int) $transferred_student->order);
        $this->assertNull($transferred_student->new_student_order_id);
        $this->assertNull($transferred_student->courses_pending_id);
        $this->assertSame($this->source_branch->id, (int) $order->fresh()->centre);
        $this->assertSame($order->getRawOriginal(), $order->fresh()->getRawOriginal());
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('branch_transfers', 1);
        $this->assertDatabaseHas('branch_transfers', [
            'subject_type' => Contact::class,
            'subject_id' => $student->id,
            'from_branch_id' => $this->source_branch->id,
            'to_branch_id' => $this->destination_branch->id,
            'actor_id' => $this->actor->id,
        ]);
    }

    #[Test]
    public function StudentTransferKeepsIdentityAndParentDetailsButStartsDestinationEnrollmentFromScratch(): void
    {
        $student = $this->Student('Existing learner');
        $student->forceFill([
            'type' => 'student',
            'ref' => 'OLD-REF',
            'code' => 'SRC-0001',
            'nickname' => 'Nok',
            'school' => 'Source School',
            'birth_date' => '2012-03-04',
            'telephone' => '0800000000',
            'father_name' => 'Father Name',
            'father_mobile' => '0811111111',
            'mother_name' => 'Mother Name',
            'mother_mobile' => '0822222222',
            'start_date' => '2026-01-10',
            'start_term' => 1,
            'level' => 2,
            'term' => 3,
            'bookuse' => 4,
            'level_name' => 'Level 2',
            'term_name' => 'Term 3',
            'bookuse_name' => 'Full book',
            'level2' => 3,
            'term2' => 1,
            'bookuse2' => 5,
            'level2_name' => 'Level 3',
            'term2_name' => 'Term 1',
            'bookuse2_name' => 'Half book',
            'free_course' => 3,
            'discontinued' => 1,
            'discontinued_date' => '2026-02-01',
            'discontinued_reason' => 'Moved away',
        ]);
        $old_order = Order::create([
            'centre' => $this->source_branch->id,
            'cid' => $student->id,
            'status' => 2,
            'total_price' => 1200,
        ]);
        $student->order = $old_order->id;
        $student->new_student_order_id = $old_order->id;
        $student->save();
        DB::table('contacts')->where('id', $student->id)->update(['courses_pending_id' => 77]);
        $history = Histrories::create([
            'centre' => $this->source_branch->id,
            'student_id' => $student->id,
            'teacher_id' => 0,
            'level_id' => 2,
            'term' => '3',
            'bookuse' => '4',
            'course_remaining' => '8',
            'date' => '2026-01-15',
            'stime' => '09:00:00',
            'etime' => '10:00:00',
            'start_date' => '2026-01-10',
            'comment' => '',
            'signature' => '',
        ]);

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload([$student]))
            ->assertOk()
            ->assertJsonPath('transferred_count', 1);

        $transferred_student = $student->fresh();
        $this->assertSame($this->destination_branch->id, (int) $transferred_student->centre);
        $this->assertSame('Existing learner', $transferred_student->name);
        $this->assertNull($transferred_student->ref);
        $this->assertSame('SRC-0001', $transferred_student->code);
        $this->assertSame('Nok', $transferred_student->nickname);
        $this->assertSame('Source School', $transferred_student->school);
        $this->assertSame('2012-03-04', $transferred_student->birth_date);
        $this->assertSame('0800000000', $transferred_student->telephone);
        $this->assertSame('Father Name', $transferred_student->father_name);
        $this->assertSame('Mother Name', $transferred_student->mother_name);
        $this->assertSame(0, (int) $transferred_student->order);
        $this->assertNull($transferred_student->new_student_order_id);
        $this->assertNull($transferred_student->courses_pending_id);
        $this->assertSame(0, (int) $transferred_student->free_course);
        $this->assertNull($transferred_student->start_date);
        $this->assertNull($transferred_student->start_term);
        $this->assertSame(0, (int) $transferred_student->level);
        $this->assertSame(0, (int) $transferred_student->term);
        $this->assertSame(0, (int) $transferred_student->bookuse);
        $this->assertNull($transferred_student->level_name);
        $this->assertNull($transferred_student->term_name);
        $this->assertNull($transferred_student->bookuse_name);
        $this->assertSame(0, (int) $transferred_student->level2);
        $this->assertSame(0, (int) $transferred_student->term2);
        $this->assertSame(0, (int) $transferred_student->bookuse2);
        $this->assertNull($transferred_student->level2_name);
        $this->assertNull($transferred_student->term2_name);
        $this->assertNull($transferred_student->bookuse2_name);
        $this->assertSame(0, (int) $transferred_student->discontinued);
        $this->assertNull($transferred_student->discontinued_date);
        $this->assertNull($transferred_student->discontinued_reason);
        $this->assertSame($this->source_branch->id, (int) $old_order->fresh()->centre);
        $this->assertSame($this->source_branch->id, (int) $history->fresh()->centre);

        $destination_role = Role::findOrCreate('DestinationStudentList', 'web');
        $destination_role->givePermissionTo(Permission::findOrCreate('student-list', 'web'));
        $destination_actor = User::factory()->create([
            'department_id' => $this->destination_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ]);
        $destination_actor->assignRole($destination_role);
        $this->actingAs($destination_actor)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/contacts?draw=1&start=0&length=10')
            ->assertOk()
            ->assertJsonFragment(['id' => $student->id]);
    }

    #[Test]
    public function StudentsWithMixedOrderStatusesMoveTogetherAndKeepOrdersUnchanged(): void
    {
        $students = [$this->Student('Without orders')];
        $orders = [];
        foreach ([0, 1, 2] as $order_status) {
            $student = $this->Student();
            $order = Order::create([
                'cid' => $student->id,
                'centre' => $this->source_branch->id,
                'status' => $order_status,
            ])->refresh();
            $student->update(['order' => $order->id]);
            $students[] = $student;
            $orders[] = $order;
        }

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
            ->assertOk()
            ->assertJsonPath('transferred_count', 4)
            ->assertJsonCount(4, 'data');

        foreach ($students as $student) {
            $this->assertSame($this->destination_branch->id, (int) $student->fresh()->centre);
            $this->assertSame(0, (int) $student->fresh()->order);
        }
        foreach ($orders as $order) {
            $this->assertSame($order->getRawOriginal(), $order->fresh()->getRawOriginal());
        }
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('branch_transfers', 4);
    }

    #[Test]
    public function AllInvalidStudentDestinationsAreReportedWithoutMovingAnyone(): void
    {
        $students = [
            $this->Student('Ready'),
            $this->Student('Already at destination', $this->destination_branch->id),
            $this->Student('Also at destination', $this->destination_branch->id),
        ];

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
            ->assertUnprocessable()
            ->assertJsonCount(2, 'record_errors')
            ->assertJsonPath('record_errors.0.record_id', $students[1]->id)
            ->assertJsonPath('record_errors.0.code', 'SAME_BRANCH')
            ->assertJsonPath('record_errors.1.record_id', $students[2]->id)
            ->assertJsonPath('record_errors.1.code', 'SAME_BRANCH');

        foreach ($students as $student) {
            $this->assertSame((int) $student->centre, (int) $student->fresh()->centre);
        }
        $this->assertDatabaseCount('branch_transfers', 0);
    }

    #[Test]
    public function StaleSourcesAndRepeatedRequestsDoNotWriteAnotherTransfer(): void
    {
        $first = $this->Student('First');
        $second = $this->Student('Second');
        $payload = $this->Payload([$first, $second]);
        DB::table('contacts')->where('id', $second->id)->update(['centre' => $this->destination_branch->id]);

        $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)
            ->assertStatus(409)
            ->assertJsonPath('record_errors.0.code', 'SOURCE_CHANGED');
        $this->assertSame($this->source_branch->id, (int) $first->fresh()->centre);
        $this->assertDatabaseCount('branch_transfers', 0);

        $single = $this->Payload([$first]);
        $this->postJson('/branch-transfers', $single)->assertOk();
        $this->postJson('/branch-transfers', $single)->assertStatus(409);
        $this->assertDatabaseCount('branch_transfers', 1);
    }

    #[Test]
    public function UserBatchesApplyTheDepartmentAndRevokeTokens(): void
    {
        $users = User::factory()->count(2)->create([
            'department_id' => $this->source_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ])->all();
        $role = Role::findOrCreate('Teacher', 'web');
        foreach ($users as $user) {
            $user->assignRole($role);
            $user->createToken('transfer-test');
        }
        $position = Position::create([
            'name' => 'Teaching',
            'department_id' => $this->destination_branch->id,
            'status' => 1,
        ]);
        $payload = $this->Payload($users, 'user') + ['position_id' => $position->id];

        $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)
            ->assertOk()
            ->assertJsonPath('transferred_count', 2);

        foreach ($users as $user) {
            $fresh_user = $user->fresh();
            $this->assertSame($this->destination_branch->id, (int) $fresh_user->department_id);
            $this->assertSame($position->id, (int) $fresh_user->position_id);
            $this->assertTrue($fresh_user->hasRole('Teacher'));
            $this->assertSame(0, $fresh_user->tokens()->count());
        }
        $this->assertDatabaseCount('branch_transfers', 2);
    }

    #[Test]
    public function LaterDatabaseFailureRollsBackUsersHistoryAndTokens(): void
    {
        $users = User::factory()->count(2)->create([
            'department_id' => $this->source_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ])->all();
        foreach ($users as $user) {
            $user->createToken('keep-on-rollback');
        }
        $audit_count = DB::table('branch_audit_logs')->count();
        $blocked_id = (int) $users[1]->id;
        DB::statement("CREATE TEMP TRIGGER reject_transfer BEFORE UPDATE ON users WHEN NEW.id = {$blocked_id} BEGIN SELECT RAISE(ABORT, 'planned write failure'); END");

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($users, 'user'));
            $this->fail('Expected the controlled second user update to fail.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString('planned write failure', $exception->getMessage());
        } finally {
            DB::statement('DROP TRIGGER reject_transfer');
        }

        foreach ($users as $user) {
            $this->assertSame($this->source_branch->id, (int) $user->fresh()->department_id);
            $this->assertSame(1, $user->tokens()->count());
        }
        $this->assertDatabaseCount('branch_transfers', 0);
        $this->assertSame($audit_count, DB::table('branch_audit_logs')->count());
        $this->assertFalse(app(BranchContext::class)->allowTransfer);
    }

    #[Test]
    public function BatchSizeAndMalformedSelectionsNeverPartiallyExecute(): void
    {
        $students = [];
        for ($student_index = 0; $student_index < 101; $student_index++) {
            $students[] = $this->Student('Batch '.$student_index);
        }

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
            ->assertUnprocessable();

        $base = $this->Payload([$students[0]]);
        $bad_payloads = [
            array_replace($base, ['record_ids' => []]),
            array_replace($base, ['record_ids' => [$students[0]->id, $students[0]->id]]),
            array_replace($base, ['record_ids' => [0]]),
            array_replace($base, ['record_ids' => ['not-an-id']]),
            array_replace($base, ['expected_from_branch_ids' => []]),
            array_replace($base, ['expected_from_branch_ids' => [$students[0]->id => 0]]),
            $base + ['record_id' => $students[0]->id],
        ];
        foreach ($bad_payloads as $bad_payload) {
            $this->postJson('/branch-transfers', $bad_payload)->assertUnprocessable();
        }
        $this->assertDatabaseCount('branch_transfers', 0);

        $this->postJson('/branch-transfers', $this->Payload(array_slice($students, 0, 100)))
            ->assertOk()
            ->assertJsonPath('transferred_count', 100);
        $this->assertSame($this->source_branch->id, (int) $students[100]->fresh()->centre);
    }

    #[Test]
    public function StudentBatchesMaySendAnExplicitNullDepartment(): void
    {
        $student = $this->Student('Null department');

        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload([$student]) + [
            'position_id' => null,
        ])->assertOk()->assertJsonPath('transferred_count', 1);

        $this->assertSame($this->destination_branch->id, (int) $student->fresh()->centre);
    }

    #[Test]
    public function BatchSuccessUsesTheSelectedLocale(): void
    {
        $expected_messages = [
            'en' => 'Transferred: 1. Existing documents remain with the issuing branch.',
            'th' => 'ย้ายแล้ว: 1 คน เอกสารเดิมยังอยู่กับสาขาผู้ออก',
        ];

        foreach ($expected_messages as $locale => $expected_message) {
            $this->actor->update(['language_code' => $locale]);
            $student = $this->Student('Localized '.$locale);
            $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload([$student]))
                ->assertOk()
                ->assertJsonPath('message', $expected_message);
        }
    }

    #[Test]
    public function InvalidDestinationsDepartmentsAndRolesCannotMoveUsers(): void
    {
        $user = User::factory()->create([
            'department_id' => $this->source_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ]);
        $user->createToken('preserve');
        $role = Role::findOrCreate('SourceOnly', 'web');
        $role->forceFill(['branch_id' => $this->source_branch->id])->save();
        $user->assignRole($role);
        $payload = $this->Payload([$user], 'user');

        $this->destination_branch->update(['status' => 0]);
        $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)->assertUnprocessable();
        $this->destination_branch->update(['status' => 1]);

        $wrong_department = Position::create([
            'name' => 'Source department',
            'department_id' => $this->source_branch->id,
            'status' => 1,
        ]);
        $this->postJson('/branch-transfers', $payload + ['position_id' => $wrong_department->id])
            ->assertUnprocessable();
        $inactive_department = Position::create([
            'name' => 'Inactive department',
            'department_id' => $this->destination_branch->id,
            'status' => 0,
        ]);
        $this->postJson('/branch-transfers', $payload + ['position_id' => $inactive_department->id])
            ->assertUnprocessable();
        $this->postJson('/branch-transfers', $payload)
            ->assertForbidden()
            ->assertJsonPath('record_errors.0.code', 'ROLE_NOT_ALLOWED');

        $this->assertSame($this->source_branch->id, (int) $user->fresh()->department_id);
        $this->assertSame(1, $user->tokens()->count());
        $this->assertDatabaseCount('branch_transfers', 0);
    }

    #[Test]
    public function RestrictedCentralActorsCannotDiscoverForeignRecords(): void
    {
        $restricted = User::factory()->create([
            'department_id' => Department::HQ_ID,
            'disable' => 0,
            'language_code' => 'en',
            'password' => bcrypt('password-123'),
        ]);
        $restricted->assignRole(Role::findOrCreate('Admin', 'web'));
        $restricted->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
        $foreign = $this->Student('Confidential Name');

        $this->actingAs($restricted)
            ->getJson('/branch-transfers/targets?paginated=1&type=student&from_branch_id='.$this->source_branch->id)
            ->assertForbidden();
        $payload = array_replace($this->Payload([$foreign]), ['to_branch_id' => Department::HQ_ID]);
        $response = $this->postJson('/branch-transfers', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('record_errors.0.code', 'RECORD_UNAVAILABLE');
        $this->assertStringNotContainsString('Confidential Name', $response->getContent());
        $this->assertDatabaseCount('branch_transfers', 0);
        $this->assertSame($this->source_branch->id, (int) $foreign->fresh()->centre);
    }

    #[Test]
    public function CentralAccountsAreExcludedAndCannotBeTransferred(): void
    {
        $this->actingAs($this->actor)
            ->getJson('/branch-transfers/targets?paginated=1&type=user')
            ->assertOk()
            ->assertJsonMissing(['id' => $this->actor->id]);
        $this->postJson('/branch-transfers', $this->Payload([$this->actor], 'user'))
            ->assertUnprocessable();
        $this->assertSame(Department::HQ_ID, (int) $this->actor->fresh()->department_id);
        $this->assertDatabaseCount('branch_transfers', 0);
    }

    #[Test]
    public function QueryBoundariesAndInactiveSourcesFollowTheContract(): void
    {
        $student = $this->Student('Ready');
        $this->actingAs($this->actor);
        foreach (['0', 'A', str_repeat('A', 101)] as $query) {
            $this->getJson('/branch-transfers/targets?paginated=1&type=student&q='.urlencode($query))
                ->assertUnprocessable();
        }
        $this->getJson('/branch-transfers/targets?paginated=1&type=student&q=')->assertOk();
        $this->getJson('/branch-transfers/targets?paginated=1&type=student&page=0')->assertUnprocessable();
        $this->postJson('/branch-transfers', array_replace($this->Payload([$student]), [
            'to_branch_id' => $this->source_branch->id,
        ]))->assertUnprocessable();
        $this->source_branch->update(['status' => 0]);
        $this->postJson('/branch-transfers', $this->Payload([$student]))->assertOk();
    }

    #[Test]
    public function LegacySingleRecordJsonAndBatchRedirectKeepTheirContracts(): void
    {
        $first = $this->Student('Legacy');
        $second = $this->Student('Form');
        $this->actingAs($this->actor)->postJson('/branch-transfers', [
            'type' => 'student',
            'record_id' => $first->id,
            'to_branch_id' => $this->destination_branch->id,
            'reason' => 'Legacy transfer',
        ])->assertExactJson([
            'success' => true,
            'data' => [
                'id' => $first->id,
                'from_branch_id' => $this->source_branch->id,
                'to_branch_id' => $this->destination_branch->id,
            ],
        ]);
        $this->post('/branch-transfers', $this->Payload([$second]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Transferred: 1. Existing documents remain with the issuing branch.');
    }

    #[Test]
    public function GuestsAndBranchActorsCannotTransfer(): void
    {
        $student = $this->Student();
        $this->postJson('/branch-transfers', $this->Payload([$student]))->assertUnauthorized();
        $branch_actor = User::factory()->create([
            'department_id' => $this->source_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ]);
        $branch_actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $branch_actor->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
        $this->actingAs($branch_actor)->postJson('/branch-transfers', $this->Payload([$student]))
            ->assertForbidden();
        $this->getJson('/branch-transfers/targets?paginated=1&type=student')->assertForbidden();
        $this->assertSame($this->source_branch->id, (int) $student->fresh()->centre);
        $this->assertDatabaseCount('branch_transfers', 0);
    }

    #[Test]
    public function MixedRecordProblemsKeepAuthorizationPrecedence(): void
    {
        $users = User::factory()->count(2)->create([
            'department_id' => $this->source_branch->id,
            'disable' => 0,
            'password' => bcrypt('password-123'),
        ])->all();
        $role = Role::findOrCreate('LocalOnly', 'web');
        $role->forceFill(['branch_id' => $this->source_branch->id])->save();
        $users[0]->assignRole($role);
        $payload = $this->Payload($users, 'user');
        DB::table('users')->where('id', $users[1]->id)->update(['department_id' => Department::HQ_ID]);

        $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)
            ->assertForbidden()
            ->assertJsonCount(2, 'record_errors')
            ->assertJsonPath('record_errors.0.code', 'ROLE_NOT_ALLOWED')
            ->assertJsonPath('record_errors.1.code', 'SOURCE_CHANGED');
        $this->assertSame($this->source_branch->id, (int) $users[0]->fresh()->department_id);
        $this->assertDatabaseCount('branch_transfers', 0);
    }

    #[Test]
    public function PageOffersInactiveSourcesButOnlyActiveDestinations(): void
    {
        $this->source_branch->update(['status' => 0]);
        $response = $this->actingAs($this->actor)->get('/branch-transfers')->assertOk();
        $document = new \DOMDocument();
        $previous_errors = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_errors);
        }
        $xpath = new \DOMXPath($document);
        $source_id = (int) $this->source_branch->id;
        $destination_id = (int) $this->destination_branch->id;
        $this->assertSame(1, $xpath->query('//select[@id="transfer-source"]/option[@value="'.$source_id.'"]')->length);
        $this->assertSame(0, $xpath->query('//select[@id="branch-id"]/option[@value="'.$source_id.'"]')->length);
        $this->assertSame(1, $xpath->query('//select[@id="branch-id"]/option[@value="'.$destination_id.'"]')->length);
        $this->assertSame(0, $xpath->query('//select[@name="record_id"]')->length);
    }
}
