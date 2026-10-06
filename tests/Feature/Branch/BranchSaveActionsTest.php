<?php

namespace Tests\Feature\Branch;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\Position;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchSaveActionsTest extends TestCase
{
    use RefreshDatabase;

    private function setupBranch(): array
    {
        $branch = Department::create(['name' => 'Test branch', 'code' => 'TB', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('secret')]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        foreach (['department-edit', 'student-edit', 'student-list', 'history-edit', 'order-edit', 'receipt-edit'] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        return [$branch, $actor];
    }

    private function student(Department $branch): array
    {
        $level = level::create(['name' => 'K1', 'price' => 5000, 'half_price' => 2500, 'book_price' => 1000, 'book_half_price' => 500]);
        $term = term::create(['name' => '1', 'level_id' => $level->id]);
        $book = new bookuse();
        $book->level_id = $level->id;
        $book->term_id = $term->id;
        $book->type = 1;
        $book->name = 'K1 T1';
        $book->qty = 12;
        $book->save();
        $student = Contact::create(['centre' => $branch->id, 'type' => 'student', 'name' => 'Original Student', 'nickname' => 'Old', 'address' => 'Old address', 'code' => 'TB001', 'start_date' => '2026-10-01', 'school' => 'School', 'level' => $level->id, 'term' => $term->id, 'bookuse' => $book->id]);
        return [$student, $level, $term, $book];
    }

    public function test_department_edit_form_and_save_persist(): void
    {
        [$branch, $actor] = $this->setupBranch();
        $position = Position::create(['department_id' => $branch->id, 'name' => 'Operations', 'status' => 1]);
        $this->actingAs($actor)->getJson('/positions/edit/'.$position->id)->assertOk()->assertJsonPath('data.name', 'Operations');
        $this->putJson('/positions/save/'.$position->id, ['name' => 'Operations Updated', 'department' => $branch->id, 'status' => 1])->assertOk()->assertJsonStructure(['success']);
        $this->assertSame('Operations Updated', $position->fresh()->name);
    }

    public function test_student_profile_changes_persist(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student, $level, $term, $book] = $this->student($branch);
        $this->actingAs($actor)->putJson('/contacts/save/'.$student->id, ['centre' => $branch->id, 'code' => $student->code, 'gender' => 1, 'discontinued' => 0, 'free_course' => 0, 'name' => $student->name, 'nickname' => 'New', 'address' => 'New address', 'start_date' => '2026-10-01', 'school' => 'School', 'level' => $level->id, 'term' => $term->id, 'bookuse' => $book->id])->assertOk()->assertJsonStructure(['success']);
        $this->assertSame('New', $student->fresh()->nickname);
        $this->assertSame('New address', $student->fresh()->address);
    }

    public function test_order_confirm_creates_invoice_and_receipt_once(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student] = $this->student($branch);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id, 'status' => 1, 'total_price' => 6000]);
        Receipt::create(['centre' => $branch->id, 'cid' => $student->id, 'oid' => $order->id, 'type' => 0, 'status' => 0, 'receipt_number' => 'PENDING-1']);
        $this->actingAs($actor)->putJson('/orders/confirm', ['id' => $order->id])->assertOk()->assertJsonPath('success', true);
        $this->assertSame(2, (int) $order->fresh()->status);
        $this->assertDatabaseHas('invoices', ['oid' => $order->id]);
        $this->assertDatabaseHas('receipts', ['oid' => $order->id]);
        $this->putJson('/orders/confirm', ['id' => $order->id]);
        $this->assertSame(1, \DB::table('invoices')->where('oid', $order->id)->count());
        $this->assertSame(1, \DB::table('receipts')->where('oid', $order->id)->where('type', 1)->count());
        $this->assertSame(1, \DB::table('receipts')->where('oid', $order->id)->where('type', 0)->count());
    }

    public function test_student_profile_edit_does_not_replace_existing_order_details(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student, $level, $term, $book] = $this->student($branch);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id, 'status' => 1, 'total_price' => 6000]);
        $student->update(['order' => $order->id]);
        $detail = OrderDetailList::create(['order_id' => $order->id, 'book' => 'K1 T1', 'level' => 'K1', 'term' => '1']);
        $this->actingAs($actor)->putJson('/contacts/save/'.$student->id, [
            'centre' => $branch->id, 'code' => $student->code, 'gender' => 1, 'discontinued' => 0,
            'free_course' => 0, 'name' => $student->name, 'nickname' => 'Changed',
            'address' => 'Changed address', 'start_date' => '2026-10-01', 'school' => 'School',
            'level' => $level->id, 'term' => $term->id, 'bookuse' => $book->id,
        ])->assertOk()->assertJsonStructure(['success']);
        $this->assertSame('Changed', $student->fresh()->nickname);
        $this->assertDatabaseHas('order_detail_lists', ['id' => $detail->id, 'order_id' => $order->id]);
    }

    public function test_receipt_billing_details_persist(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student] = $this->student($branch);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id]);
        $receipt = Receipt::create(['centre' => $branch->id, 'cid' => $student->id, 'oid' => $order->id, 'receipt_number' => 'TB-R1', 'total_fee' => 100]);
        $this->actingAs($actor)->putJson('/receipts/save/'.$receipt->id, ['receipt_number' => 'TB-R1', 'receipt_date' => '2026-10-06', 'billing_details' => 'Custom billing address', 'total' => 100, 'des' => ['Tuition']])->assertOk()->assertJsonStructure(['success']);
        $this->assertSame('Custom billing address', $receipt->fresh()->billing_details);
    }

    public function test_book_receive_updates_summary(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student] = $this->student($branch);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id]);
        $book = OrderDetailList::create(['order_id' => $order->id, 'book' => 'K1 T1']);
        $this->actingAs($actor)->putJson('/contacts/'.$student->id.'/book-receipts/'.$book->id)->assertOk()->assertJsonPath('data.summary.received_count', 1);
        $this->assertNotNull($book->fresh()->received_at);
        $list = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->getJson('/contacts?draw=1&start=0&length=10');
        $list->assertOk();
        $this->assertStringContainsString('1/1', $list->json('data.0.book_receipt'));
    }

    public function test_history_comment_persists_for_single_book_enrolment(): void
    {
        [$branch, $actor] = $this->setupBranch();
        [$student, $level, $term, $book] = $this->student($branch);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id]);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $book->id, 'pname' => $book->name, 'quantity' => 1, 'price' => 5000]);
        OrderDetailList::create(['order_id' => $order->id, 'level' => $level->name, 'term' => $term->name, 'book' => $book->name]);
        $history = Histrories::create(['centre' => $branch->id, 'student_id' => $student->id, 'teacher_id' => $actor->id, 'level_id' => $level->id, 'level_name' => $level->name, 'term' => $term->id, 'bookuse' => $book->id, 'course_remaining' => 10, 'date' => '2026-10-06', 'stime' => '10:00', 'etime' => '11:00', 'start_date' => '2026-10-01', 'end_date' => '2026-12-01', 'comment' => 'Old', 'signature' => 'existing.png']);
        $this->actingAs($actor)->putJson('/history/update/'.$history->id, ['level_id' => $level->id, 'level_name' => $level->name, 'term' => $term->id, 'bookuse' => $book->id, 'teacher_id' => $actor->id, 'date' => '2026-10-06', 'stime' => '10:00', 'etime' => '11:00', 'start_date' => '2026-10-01', 'end_date' => '2026-12-01', 'comment' => 'New note'])->assertOk()->assertJsonStructure(['success']);
        $this->assertSame('New note', $history->fresh()->comment);
    }
}
