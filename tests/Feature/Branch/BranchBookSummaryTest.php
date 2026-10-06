<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchBookSummaryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function TransferredStudentListCountsOnlyBooksVisibleInTheDetails(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $hq = Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $source_branch = Department::create(['name' => 'Source', 'code' => 'SRC', 'status' => 1]);
        $destination_branch = Department::create(['name' => 'Destination', 'code' => 'DST', 'status' => 1]);
        $owner = $this->Actor($hq, 'SystemAdmin');
        $student = Contact::create([
            'centre' => $source_branch->id,
            'code' => 'SRC0001',
            'name' => 'Transferred student',
            'type' => 'student',
            'discontinued' => 0,
        ]);
        $old_order = Order::create(['centre' => $source_branch->id, 'cid' => $student->id, 'status' => 2]);
        OrderDetailList::create(['order_id' => $old_order->id, 'book' => 'Source book', 'received_at' => now()]);

        $this->actingAs($owner)->postJson('/branch-transfers', [
            'type' => 'student',
            'record_id' => $student->id,
            'to_branch_id' => $destination_branch->id,
            'reason' => 'Student changed branch',
        ])->assertOk();

        $new_order = Order::create(['centre' => $destination_branch->id, 'cid' => $student->id, 'status' => 2]);
        OrderDetailList::create(['order_id' => $new_order->id, 'book' => 'Received destination book', 'received_at' => now()]);
        OrderDetailList::create(['order_id' => $new_order->id, 'book' => 'Pending destination book']);
        OrderDetailList::create(['order_id' => $new_order->id, 'book' => ' ']);
        $branch_actor = $this->Actor($destination_branch, 'BranchAdmin');

        $details = $this->actingAs($branch_actor)->getJson('/contacts/'.$student->id.'/book-receipts')
            ->assertOk()
            ->assertJsonPath('data.summary.total_count', 2)
            ->assertJsonPath('data.summary.received_count', 1);
        $this->assertSame([$new_order->id], array_column($details->json('data.orders'), 'id'));

        $list = $this->getJson('/contacts', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $student_row = collect($list->json('data'))->firstWhere('id', $student->id);
        $this->assertNotNull($student_row);
        $this->assertSame(2, (int) $student_row['book_receipt_total_count']);
        $this->assertSame(1, (int) $student_row['book_receipt_received_count']);

        $this->actingAs($owner)->getJson('/contacts/'.$student->id.'/book-receipts')
            ->assertOk()->assertJsonPath('data.summary.total_count', 3)->assertJsonPath('data.summary.received_count', 2);
        $owner_list = $this->getJson('/contacts', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $owner_row = collect($owner_list->json('data'))->firstWhere('id', $student->id);
        $this->assertNotNull($owner_row);
        $this->assertSame(3, (int) $owner_row['book_receipt_total_count']);
        $this->assertSame(2, (int) $owner_row['book_receipt_received_count']);
    }

    private function Actor(Department $branch, string $role_name): User
    {
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('branch-test-password')]);
        $actor->assignRole(Role::findOrCreate($role_name, 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('student-list', 'web'));

        return $actor;
    }
}
