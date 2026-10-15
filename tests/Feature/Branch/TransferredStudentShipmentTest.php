<?php

namespace Tests\Feature\Branch;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransferredStudentShipmentTest extends TestCase
{
    use RefreshDatabase;

    private Department $rp;
    private Department $sb;
    private User $owner;
    private User $rpAdmin;
    private User $sbAdmin;
    private Contact $student;
    private Order $originalOrder;
    private OrderDetailList $originalBook;
    private level $level;
    private term $term;
    private bookuse $book;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->travelTo(now()->setDate(2026, 10, 1)->setTime(9, 0));
        $hq = Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        $this->rp = Department::create(['name' => 'RP', 'code' => 'RP', 'status' => 1]);
        $this->sb = Department::create(['name' => 'SB', 'code' => 'SB', 'status' => 1]);
        foreach (['branch-transfer', 'student-edit', 'order-edit', 'book-shipment-create', 'book-shipment-list', 'book-shipment-cancel'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $this->owner = $this->makeActor($hq, 'SystemAdmin');
        $this->rpAdmin = $this->makeActor($this->rp, 'BranchAdmin');
        $this->sbAdmin = $this->makeActor($this->sb, 'BranchAdmin');
        $this->rpAdmin->givePermissionTo('student-edit');
        $this->sbAdmin->givePermissionTo('student-edit');
        $this->sbAdmin->givePermissionTo('order-edit');
        $this->level = level::create(['name' => 'K1', 'price' => 5000, 'half_price' => 2500, 'book_price' => 1000, 'book_half_price' => 500]);
        $this->term = term::create(['name' => '1', 'level_id' => $this->level->id]);
        $this->book = new bookuse;
        $this->book->forceFill(['level_id' => $this->level->id, 'term_id' => $this->term->id, 'type' => 1, 'name' => 'G11-G15', 'qty' => 12])->save();
        $this->student = Contact::create(['centre' => $this->rp->id, 'name' => 'Transferred learner', 'nickname' => 'Oab', 'type' => 'student', 'code' => 'RP0001']);
        $this->originalOrder = Order::create(['centre' => $this->rp->id, 'cid' => $this->student->id, 'order_number' => 'RP-original', 'status' => 2]);
        $this->originalBook = OrderDetailList::create(['order_id' => $this->originalOrder->id, 'book' => 'Original RP book', 'level' => 'K1', 'term' => '1']);
        $this->student->update(['order' => $this->originalOrder->id, 'new_student_order_id' => $this->originalOrder->id]);
    }

    public function test_first_destination_enrolment_becomes_eligible_after_transfer_and_receipt_route(): void
    {
        $this->transferTo($this->sb);
        $this->assertNull($this->student->fresh()->new_student_order_id);
        $order = $this->enrolAt($this->sb);

        $this->actingAs($this->sbAdmin)->putJson('/orders/save/'.$order->id, [
            'order_number' => $order->order_number, 'cid' => $this->student->id,
            'status' => 1, 'total_cost' => 6000, 'payment' => 1,
            'refund' => 0, 'register_fee' => 0, 'access_fee' => 0,
            'orther_fee' => 0, 'discount' => 0, 'discount_book' => 0,
        ])->assertOk()->assertJsonPath('status', 1);
        $this->assertDatabaseHas('receipts', [
            'centre' => $this->sb->id, 'cid' => $this->student->id,
            'student_no' => $this->student->fresh()->code, 'oid' => $order->id,
        ]);

        $this->assertSame($order->id, (int) $this->student->fresh()->new_student_order_id);
        $response = $this->candidates($this->sb)->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.student_id', $this->student->id);
        $this->assertSame($this->bookIds($order), $this->candidateBookIds($response));
        $this->assertNotContains($this->originalBook->id, $this->candidateBookIds($response));
        $this->assertSame($this->rp->id, (int) $this->originalOrder->fresh()->centre);
    }

    public function test_transfer_without_destination_enrolment_does_not_offer_source_branch_books(): void
    {
        $this->transferTo($this->sb);
        $this->candidates($this->sb)->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
        $this->candidates($this->rp)->assertJsonCount(0, 'data');
        $this->assertDatabaseHas('order_detail_lists', ['id' => $this->originalBook->id, 'order_id' => $this->originalOrder->id]);
    }

    public function test_continuation_order_does_not_replace_first_destination_order_or_appear_as_new_student_books(): void
    {
        $this->transferTo($this->sb);
        $first = $this->enrolAt($this->sb);
        $continuation = $this->enrolAt($this->sb);

        $this->assertSame($first->id, (int) $this->student->fresh()->new_student_order_id);
        $this->assertSame($continuation->id, (int) $this->student->fresh()->order);
        $this->assertSame($this->bookIds($first), $this->candidateBookIds($this->candidates($this->sb)));
    }

    public function test_migration_restores_first_destination_order_once_and_is_idempotent(): void
    {
        $this->transferTo($this->sb);
        $first = $this->enrolAt($this->sb);
        $this->enrolAt($this->sb);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        $this->repairLegacyPointers();
        $before = (array) DB::table('contacts')->find($this->student->id);

        $this->assertSame($first->id, (int) $this->student->fresh()->new_student_order_id);
        $this->repairLegacyPointers();
        $this->assertSame($before, (array) DB::table('contacts')->find($this->student->id), 'Running the repair twice must leave the record unchanged.');
        $this->assertSame($this->bookIds($first), $this->candidateBookIds($this->candidates($this->sb)));
        $this->assertSame($before, (array) DB::table('contacts')->find($this->student->id), 'Candidate GET must remain read-only.');
    }

    public function test_repaired_legacy_order_can_be_claimed_once_and_same_draft_can_still_see_its_books(): void
    {
        $this->transferTo($this->sb);
        $first = $this->enrolAt($this->sb);
        $this->enrolAt($this->sb);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        $this->repairLegacyPointers();
        $payload = ['destination_centre_id' => $this->sb->id, 'source_book_ids' => $this->bookIds($first)];
        $draft = $this->actingAs($this->owner)->postJson('/book-shipments', $payload)->assertCreated();
        $draftId = $draft->json('data.shipment.id');

        $this->candidates($this->sb)->assertJsonCount(0, 'data');
        $this->assertSame($this->bookIds($first), $this->candidateBookIds($this->candidates($this->sb, ['draft_shipment_id' => $draftId])));
        $this->actingAs($this->owner)->postJson('/book-shipments', $payload)
            ->assertConflict()->assertJsonPath('error.code', 'BOOK_SHIPMENT_SOURCE_CONFLICT');
        $this->assertDatabaseCount('book_shipments', 1);
        $this->assertDatabaseCount('book_shipment_items', count($this->bookIds($first)));
    }

    public function test_repaired_initial_order_does_not_promote_continuation_when_books_were_received(): void
    {
        $this->transferTo($this->sb);
        $first = $this->enrolAt($this->sb);
        $this->enrolAt($this->sb);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        DB::table('order_detail_lists')->where('order_id', $first->id)->update(['received_at' => now()]);
        $this->repairLegacyPointers();

        $this->assertSame($first->id, (int) $this->student->fresh()->new_student_order_id);
        $this->candidates($this->sb)->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
    }

    public function test_returning_to_original_branch_starts_new_episode_without_reviving_original_books(): void
    {
        $this->transferTo($this->sb);
        $this->enrolAt($this->sb);
        $this->transferTo($this->rp);
        $this->candidates($this->rp)->assertJsonCount(0, 'data');
        $returnOrder = $this->enrolAt($this->rp);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        $this->repairLegacyPointers();

        $this->assertSame($this->bookIds($returnOrder), $this->candidateBookIds($this->candidates($this->rp)));
        $this->candidates($this->sb)->assertJsonCount(0, 'data');
        $this->assertDatabaseHas('order_detail_lists', ['id' => $this->originalBook->id]);
    }

    public function test_second_transfer_to_same_destination_uses_latest_arrival_instead_of_earlier_destination_order(): void
    {
        $this->transferTo($this->sb);
        $oldSbOrder = $this->enrolAt($this->sb);
        $this->transferTo($this->rp);
        $this->enrolAt($this->rp);
        $this->transferTo($this->sb);
        $this->candidates($this->sb)->assertJsonCount(0, 'data');
        $latestSbOrder = $this->enrolAt($this->sb);
        $this->enrolAt($this->sb);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        $this->repairLegacyPointers();

        $ids = $this->candidateBookIds($this->candidates($this->sb));
        $this->assertSame($this->bookIds($latestSbOrder), $ids);
        $this->assertSame([], array_values(array_intersect($this->bookIds($oldSbOrder), $ids)));
    }

    public function test_forced_source_branch_book_selection_is_rejected_even_when_destination_has_eligible_order(): void
    {
        $this->transferTo($this->sb);
        $this->enrolAt($this->sb);
        $this->actingAs($this->owner)->postJson('/book-shipments', [
            'destination_centre_id' => $this->sb->id, 'source_book_ids' => [$this->originalBook->id],
        ])->assertUnprocessable()->assertJsonPath('error.code', 'BOOK_SHIPMENT_SOURCE_INELIGIBLE');
        $this->assertDatabaseCount('book_shipments', 0);
        $this->assertNull($this->originalBook->fresh()->active_book_shipment_item_id);
    }

    public function test_deleting_initial_order_does_not_dynamically_promote_continuation_in_candidates(): void
    {
        $this->transferTo($this->sb);
        $first = $this->enrolAt($this->sb);
        $continuation = $this->enrolAt($this->sb);
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        $this->repairLegacyPointers();
        DB::table('order_detail_lists')->where('order_id', $first->id)->delete();
        DB::table('order_details')->where('order_id', $first->id)->delete();
        DB::table('orders')->where('id', $first->id)->delete();

        // This legacy column has ON DELETE SET NULL on MySQL but no FK on SQLite.
        // Reproduce the production deletion effect before checking read-only eligibility.
        DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);

        $this->assertNull($this->student->fresh()->new_student_order_id);
        $this->assertDatabaseHas('orders', ['id' => $continuation->id]);
        $this->candidates($this->sb)->assertJsonCount(0, 'data');
    }

    /** @dataProvider migrationSafetyCases */
    public function test_migration_leaves_unproven_or_already_linked_enrolments_unchanged(string $scenario): void
    {
        if ($scenario === 'same_second_return') {
            // An original RP order and an RP -> SB -> RP round trip share one second.
            // Timestamp order alone cannot prove that this belongs to the new RP visit.
            $this->insertLegacyTransfer($this->rp, $this->sb, $this->originalOrder->created_at);
            $this->insertLegacyTransfer($this->sb, $this->rp, $this->originalOrder->created_at);
        } elseif ($scenario !== 'no_transfer') {
            $this->transferTo($this->sb);
            $first = $this->enrolAt($this->sb);

            if ($scenario === 'valid_pointer') {
                $this->enrolAt($this->sb);
            } elseif ($scenario === 'wrong_centre_order') {
                DB::table('orders')->where('id', $first->id)->update(['centre' => $this->rp->id]);
            } elseif ($scenario === 'missing_current_order') {
                DB::table('contacts')->where('id', $this->student->id)->update(['order' => 999999]);
            } elseif ($scenario === 'latest_transfer_wrong_centre') {
                $this->insertLegacyTransfer($this->sb, $this->rp, now());
            }
        }

        if ($scenario !== 'valid_pointer') {
            DB::table('contacts')->where('id', $this->student->id)->update(['new_student_order_id' => null]);
        }
        $before = (array) DB::table('contacts')->find($this->student->id);
        $this->repairLegacyPointers();

        $this->assertSame($before, (array) DB::table('contacts')->find($this->student->id));
        if ($scenario !== 'valid_pointer') {
            $this->assertNull($this->student->fresh()->new_student_order_id);
        }
    }

    public static function migrationSafetyCases(): array
    {
        return [
            'no transfer history' => ['no_transfer'],
            'existing valid pointer' => ['valid_pointer'],
            'current order belongs to another centre' => ['wrong_centre_order'],
            'current order does not exist' => ['missing_current_order'],
            'latest transfer disagrees with current centre' => ['latest_transfer_wrong_centre'],
            'same-second return cannot identify current visit' => ['same_second_return'],
        ];
    }

    public function test_stale_hq_enrolment_form_cannot_create_order_in_previous_centre(): void
    {
        $this->transferTo($this->sb);
        $before = (array) DB::table('contacts')->find($this->student->id);
        $orderCount = DB::table('orders')->count();
        $detailCount = DB::table('order_details')->count();
        $bookCount = DB::table('order_detail_lists')->count();

        $this->actingAs($this->owner)->postJson('/contacts/receipt', [
            'centre' => $this->rp->id, 'student' => $this->student->id,
            'level' => $this->level->id, 'term' => $this->term->id, 'bookuse' => $this->book->id,
            'level2' => $this->level->id, 'term2' => $this->term->id, 'bookuse2' => $this->book->id,
            'ref' => 'Stale pre-transfer form', 'free_course' => 0,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', $orderCount);
        $this->assertDatabaseCount('order_details', $detailCount);
        $this->assertDatabaseCount('order_detail_lists', $bookCount);
        $this->assertSame($before, (array) DB::table('contacts')->find($this->student->id));
    }

    private function insertLegacyTransfer(Department $from, Department $to, $createdAt): void
    {
        DB::table('branch_transfers')->insert([
            'subject_type' => Contact::class, 'subject_id' => $this->student->id,
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'actor_id' => $this->owner->id, 'reason' => 'Legacy migration fixture',
            'created_at' => $createdAt,
        ]);
    }

    private function repairLegacyPointers(): void
    {
        $migration = require database_path('migrations/2026_10_15_000001_restore_transferred_student_initial_orders.php');
        $migration->up();
    }

    private function makeActor(Department $branch, string $roleName): User
    {
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'language_code' => 'en', 'password' => bcrypt('shipment-test-password')]);
        $actor->assignRole(Role::findOrCreate($roleName, 'web'));
        return $actor;
    }

    private function transferTo(Department $destination): void
    {
        $this->travel(1)->days();
        $this->actingAs($this->owner)->postJson('/branch-transfers', [
            'type' => 'student', 'record_id' => $this->student->id,
            'to_branch_id' => $destination->id, 'reason' => 'Shipment regression transfer',
        ])->assertOk();
        $this->student->refresh();
    }

    private function enrolAt(Department $branch): Order
    {
        $this->travel(1)->days();
        $actor = $branch->is($this->sb) ? $this->sbAdmin : $this->rpAdmin;
        $response = $this->actingAs($actor)->postJson('/contacts/receipt', [
            'centre' => $branch->id, 'student' => $this->student->id,
            'level' => $this->level->id, 'term' => $this->term->id, 'bookuse' => $this->book->id,
            'level2' => $this->level->id, 'term2' => $this->term->id, 'bookuse2' => $this->book->id,
            'ref' => 'Transfer enrolment', 'free_course' => 0,
        ])->assertOk()->assertJsonStructure(['success', 'oid']);
        $this->student->refresh();
        return Order::findOrFail($response->json('oid'));
    }

    private function candidates(Department $branch, array $extra = []): TestResponse
    {
        return $this->actingAs($this->owner)->getJson('/book-shipments/candidates?'.http_build_query(array_merge([
            'destination_centre_id' => $branch->id, 'per_page' => 20,
        ], $extra)))->assertOk();
    }

    private function bookIds(Order $order): array
    {
        return OrderDetailList::where('order_id', $order->id)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function candidateBookIds(TestResponse $response): array
    {
        $ids = collect($response->json('data'))->flatMap(fn ($student) => array_column($student['books'], 'source_book_id'))->map(fn ($id) => (int) $id)->sort()->values()->all();
        return $ids;
    }
}
