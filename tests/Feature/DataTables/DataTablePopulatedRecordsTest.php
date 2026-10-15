<?php

namespace Tests\Feature\DataTables;

use App\Models\{Contact, Department, Invoice, Order, OrderDetail, Parents, Receipt, User, bookuse, level, term};
use Spatie\Permission\Models\{Permission, Role};

class DataTablePopulatedRecordsTest extends DataTableTestCase
{
    public function test_graduated_students_return_all_columns_and_sort_real_rows(): void
    {
        $level = level::forceCreate(['name' => 'Last level', 'price' => 100, 'status' => 1]);
        $term = term::forceCreate(['name' => 'Term 1', 'status' => 1]);
        $book = bookuse::forceCreate(['name' => 'Last book', 'level_id' => $level->id, 'term_id' => $term->id]);
        foreach (['Alpha', 'Zulu'] as $i => $name) {
            $students[] = Contact::forceCreate(['centre' => 1, 'name' => $name, 'nickname' => $name.' nickname', 'code' => 'ST'.(2-$i), 'level' => $level->id, 'bookuse' => $book->id, 'start_term' => $term->id, 'term' => $term->id, 'discontinued' => 0])->id;
        }
        $this->assertSame(2, $this->table('graduated')['recordsTotal']);
        $this->assertOrder('graduated', 'name', $students);
        $this->assertOrder('graduated', 'code', array_reverse($students));
        foreach ($this->profile('graduated')['columns'] as $column) {
            if ($column['orderable']) { $this->sort('graduated', $column['data'], 'desc'); }
        }
    }

    public function test_populated_pending_documents_keep_columns_next_book_prices_and_numeric_order(): void
    {
        $level = level::forceCreate(['name' => 'K1', 'price' => 987.50, 'status' => 1]);
        $book = bookuse::forceCreate(['name' => 'Book 1', 'level_id' => $level->id, 'term_id' => 1]);
        $nextBook = bookuse::forceCreate(['name' => 'Book 2', 'level_id' => $level->id, 'term_id' => 2]);
        $paid = [];
        foreach ([100.25, 9999.50] as $i => $fee) {
            $student = Contact::create(['centre' => 1, 'name' => 'Student '.$i, 'code' => 'FIN'.$i, 'start_term' => 1]);
            $order = Order::create(['centre' => 1, 'cid' => $student->id, 'order_number' => 'ORDER-'.$i, 'total_price' => $fee, 'orther_fee' => 0]);
            OrderDetail::create(['order_id' => $order->id, 'product_id' => $book->id, 'pname' => 'Book 1', 'quantity' => 1, 'price' => $fee]);
            $paid[] = Invoice::forceCreate(['centre' => 1, 'cid' => $student->id, 'oid' => $order->id, 'invoice_number' => 'PAID-'.$i, 'invoice_date' => '2026-10-15', 'total_fee' => $fee, 'status' => 1])->id;
            Invoice::forceCreate(['centre' => 1, 'cid' => $student->id, 'oid' => $order->id, 'invoice_date' => '2026-10-15', 'total_fee' => $fee, 'status' => 0]);
            Receipt::forceCreate(['centre' => 1, 'cid' => $student->id, 'oid' => $order->id, 'total_fee' => $fee, 'type' => 1, 'status' => 0]);
        }
        $this->assertOrder('invoices', 'total_fee', $paid);
        foreach (['invoices', 'pending-invoices', 'pending-term-receipts'] as $key) {
            $initial = $this->table($key);
            $this->assertSame(2, $initial['recordsTotal'], $key);
            if ($key !== 'invoices') {
                $this->assertSame($nextBook->name, $initial['data'][0]['next_book_name']);
                $this->assertEquals(987.50, $initial['data'][0]['next_book_price']);
            }
            foreach ($this->profile($key)['columns'] as $column) {
                if ($column['orderable']) { $this->sort($key, $column['data'], 'asc'); $this->sort($key, $column['data'], 'desc'); }
            }
            $this->assertSame([], $this->search($key, 'zz-no-match-datatable')['data']);
        }
    }

    public function test_parent_student_code_search_uses_the_relationship_and_keeps_branch_scope(): void
    {
        $branch = Department::create(['name' => 'Other branch', 'code' => 'B', 'status' => 1]);
        $studentA = Contact::create(['centre' => 1, 'name' => 'Same student name', 'code' => 'A-STUDENT']);
        $studentB = Contact::create(['centre' => $branch->id, 'name' => 'Same student name', 'code' => 'B-STUDENT']);
        $parentA = Parents::create(['centre_id' => 1, 'student_id' => (string) $studentA->id, 'fname' => 'Parent A', 'lname' => 'Test']);
        $parentB = Parents::create(['centre_id' => $branch->id, 'student_id' => (string) $studentB->id, 'fname' => 'Parent B', 'lname' => 'Test']);
        $result = $this->table('parents', ['student_code_search' => 'A-STUDENT']);
        $this->assertSame(2, $result['recordsTotal']);
        $this->assertSame(1, $result['recordsFiltered']);
        $this->assertSame([$parentA->id], array_column($result['data'], 'id'));
        $actor = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'department_id' => $branch->id, 'disable' => 0]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('parent-list', 'web'));
        $this->actingAs($actor);
        $this->assertSame([], $this->table('parents', ['student_code_search' => 'A-STUDENT'])['data']);
        $this->assertSame([$parentB->id], array_column($this->table('parents', ['student_code_search' => 'B-STUDENT'])['data'], 'id'));
    }
}
