<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\CrmLead;
use App\Models\Department;
use App\Models\FileStore;
use App\Models\Order;
use App\Models\Parents;
use App\Models\Product;
use App\Models\User;
use App\Models\studentRunningNumber;
use App\Models\OrderRunningNumber;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $a;
    private Department $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        $this->a = Department::create(['name' => 'A', 'code' => 'AA', 'status' => 1]);
        $this->b = Department::create(['name' => 'B', 'code' => 'BB', 'status' => 1]);
    }

    private function actor(string $role = 'BranchAdmin', ?Department $branch = null, array $permissions = []): User
    {
        $actor = User::factory()->create(['department_id' => ($branch ?? $this->a)->id, 'disable' => 0, 'password' => bcrypt('password-123')]);
        $actor->assignRole(Role::findOrCreate($role, 'web'));
        foreach ($permissions as $name) { $actor->givePermissionTo(Permission::findOrCreate($name, 'web')); }
        return $actor;
    }

    public function test_branch_admin_reuses_shared_role_for_new_staff_in_own_branch(): void
    {
        $actor = $this->actor(permissions: ['user-create', 'student-list']);
        Role::findByName('Teacher')->givePermissionTo('student-list');
        $this->actingAs($actor)->postJson('/users', [
            'name' => 'Local Teacher', 'email' => 'teacher@example.test', 'role' => 'Teacher',
            'department_id' => $this->a->id, 'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertOk();
        $teacher = User::where('email', 'teacher@example.test')->firstOrFail();
        $this->assertTrue($teacher->hasRole('Teacher'));
        $this->assertSame($this->a->id, (int) $teacher->department_id);
        $this->assertSame(1, Role::where('name', 'Teacher')->count());
    }

    public function test_branch_local_role_cannot_be_viewed_or_assigned_by_another_branch(): void
    {
        $permission = Permission::findOrCreate('student-list', 'web');
        $a = $this->actor(permissions: ['role-create', 'student-list']);
        $this->actingAs($a)->postJson('/roles', ['name' => 'Front desk', 'branch_id' => $this->a->id, 'permission' => [$permission->id]])->assertOk();
        $role = Role::where('display_name', 'Front desk')->firstOrFail();
        $b = $this->actor(branch: $this->b, permissions: ['role-list', 'user-create', 'student-list']);
        $this->actingAs($b)->getJson('/roles/template/'.$role->id)->assertNotFound();
        $this->postJson('/users', ['name' => 'Wrong role', 'email' => 'wrong-role@example.test',
            'role' => $role->name, 'department_id' => $this->b->id,
            'password' => 'password-123', 'password_confirmation' => 'password-123'])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'wrong-role@example.test']);
    }

    public function test_stock_is_scoped_and_overdraw_does_not_change_balance_or_ledger(): void
    {
        $actor = $this->actor(permissions: ['product-edit']);
        $own = Product::create(['centre' => $this->a->id, 'name' => 'Own books', 'amount' => 8]);
        $other = Product::create(['centre' => $this->b->id, 'name' => 'Other books', 'amount' => 8]);
        $this->actingAs($actor)->putJson('/products/rm_stock/'.$own->id, ['amount' => 3, 'centre' => $this->b->id])->assertOk();
        $this->assertEquals(5, $own->fresh()->amount);
        $this->assertDatabaseHas('stocks', ['product_id' => $own->id, 'centre' => $this->a->id, 'rm_stock' => 3, 'in_stock' => 5]);
        $this->putJson('/products/rm_stock/'.$own->id, ['amount' => 6])->assertUnprocessable();
        $this->putJson('/products/add_stock/'.$other->id, ['amount' => 3])->assertForbidden();
        $this->assertEquals(5, $own->fresh()->amount);
        $this->assertEquals(8, $other->fresh()->amount);
        $this->assertSame(1, DB::table('stocks')->count());
    }

    public function test_receipt_attachment_cannot_be_deleted_using_another_orders_id(): void
    {
        $actor = $this->actor(permissions: ['receipt-delete']);
        $first = Order::create(['centre' => $this->a->id]);
        $second = Order::create(['centre' => $this->a->id]);
        $file = FileStore::forceCreate(['module' => 'orders', 'module_id' => $second->id, 'filename' => 'does-not-exist.pdf']);
        $this->actingAs($actor)->putJson('/receipts/delete/img/'.$first->id.'/'.$file->id)->assertNotFound();
        $this->assertDatabaseHas('file_stores', ['id' => $file->id]);
    }

    public function test_private_file_requires_branch_access_and_preserves_original_url(): void
    {
        $order = Order::create(['centre' => $this->a->id]);
        $filename = 'branch-test-'.Str::uuid().'.txt';
        FileStore::forceCreate(['module' => 'orders', 'module_id' => $order->id, 'filename' => $filename]);
        $path = public_path('file_store/'.$filename);
        if (!is_dir(dirname($path))) { mkdir(dirname($path), 0755, true); }
        file_put_contents($path, 'private test document');
        try {
            $this->get('/file_store/'.$filename)->assertNotFound();
            $tokenOwner = $this->actor(permissions: ['order-show']);
            $this->withToken($tokenOwner->createToken('test-private-file')->plainTextToken)
                ->get('/file_store/'.$filename)->assertOk();
            $this->withHeaders(['Authorization' => '']);
            app('auth')->forgetGuards();

            $b = $this->actor(branch: $this->b, permissions: ['order-show']);
            $this->actingAs($b)->get('/file_store/'.$filename)->assertNotFound();
            $a = $this->actor(permissions: ['order-show']);
            $this->actingAs($a)->get('/file_store/'.$filename)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        } finally {
            unlink($path);
        }
    }

    private function challenge(Contact $child): string
    {
        $id = (string) Str::uuid();
        DB::table('parent_registration_challenges')->insert([
            'id' => $id, 'email' => 'parent@example.test', 'phone' => '0800000000', 'relationship' => 'father',
            'student_ids' => json_encode([$child->id]), 'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $id;
    }

    private function registration(array $extra = []): array
    {
        return $extra + ['code' => '123456', 'password' => 'password-123', 'password_confirmation' => 'password-123'];
    }

    public function test_parent_registration_requires_proof_and_ignores_submitted_student_ids(): void
    {
        $child = Contact::create(['centre' => $this->a->id, 'father_name' => 'Test Parent', 'father_email' => 'parent@example.test', 'father_mobile' => '0800000000']);
        $other = Contact::create(['centre' => $this->b->id]);
        $id = $this->challenge($child);
        $this->postJson('/api/parent/check-user', $this->registration(['verification_id' => $id, 'student_ids' => [$other->id]]))->assertOk();
        $parent = Parents::where('email', 'parent@example.test')->firstOrFail();
        $this->assertSame([$child->id], $parent->students()->pluck('id')->all());
        $this->assertTrue(Hash::check('password-123', $parent->password));
        $this->postJson('/api/parent/check-user', $this->registration(['verification_id' => $id]))->assertUnprocessable();
        $this->assertSame(1, Parents::count());
    }

    public function test_failed_parent_verification_attempts_persist_and_lock_the_challenge(): void
    {
        $child = Contact::create(['centre' => $this->a->id]);
        $id = $this->challenge($child);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/parent/check-user', $this->registration(['verification_id' => $id, 'code' => '999999']))->assertUnprocessable();
        }
        $this->assertDatabaseHas('parent_registration_challenges', ['id' => $id, 'attempts' => 5]);
        $this->postJson('/api/parent/check-user', $this->registration(['verification_id' => $id]))->assertUnprocessable();
        $this->assertSame(0, Parents::count());
    }

    public function test_central_assignment_moves_lead_from_queue_to_only_its_branch(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $lead = CrmLead::create(['parent_name' => 'Lead parent', 'phone' => '0800000000', 'student_name' => 'Student', 'student_grade' => 'P1', 'lead_status' => 'new_lead']);
        $this->actingAs($owner)->post('/crm/leads/'.$lead->id.'/branch', ['branch_id' => $this->b->id, 'reason' => 'Customer selected this branch'])->assertRedirect();
        $a = $this->actor();
        $b = $this->actor(branch: $this->b);
        $context = app(BranchContext::class);
        $this->assertFalse($context->runAs($a, fn () => CrmLead::whereKey($lead->id)->exists()));
        $this->assertTrue($context->runAs($b, fn () => CrmLead::whereKey($lead->id)->exists()));
    }

    public function test_student_transfer_preserves_issued_document_branch_and_clears_current_enrolment(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $child = Contact::create(['centre' => $this->a->id]);
        $order = Order::create(['centre' => $this->a->id, 'cid' => $child->id, 'status' => 2]);
        $child->update(['order' => $order->id]);
        $this->actingAs($owner)->postJson('/branch-transfers', [
            'type' => 'student', 'record_id' => $child->id, 'to_branch_id' => $this->b->id, 'reason' => 'Family moved to another district',
        ])->assertOk();
        $this->assertEquals($this->b->id, $child->fresh()->centre);
        $this->assertSame(0, (int) $child->fresh()->order);
        $this->assertEquals($this->a->id, $order->fresh()->centre);
        $this->assertDatabaseHas('branch_transfers', ['subject_id' => $child->id, 'from_branch_id' => $this->a->id, 'to_branch_id' => $this->b->id]);
    }

    public function test_last_owner_cannot_delete_own_profile_or_lose_session(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $this->actingAs($owner)->deleteJson('/profile', ['password' => 'password-123'])->assertUnprocessable();
        $this->assertAuthenticatedAs($owner);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_running_numbers_are_branch_specific_and_preview_does_not_reserve_a_number(): void
    {
        $this->assertSame('AA0001', studentRunningNumber::pre_generate((string) $this->a->id));
        $this->assertSame(0, studentRunningNumber::count());
        $this->assertSame('AA0001', studentRunningNumber::generate((string) $this->a->id));
        $this->assertSame('AA0002', studentRunningNumber::generate((string) $this->a->id));
        $this->assertSame('BB0001', studentRunningNumber::generate((string) $this->b->id));
        $this->assertSame('Or-AA'.date('ym').'0001', OrderRunningNumber::generate((string) $this->a->id));
    }


    public function test_role_template_command_previews_then_applies_without_creating_branch_clones(): void
    {
        $original = Role::findByName('BranchAdmin')->permissions->pluck('name')->all();
        $this->artisan('branch:roles', ['--role' => ['BranchAdmin']])->assertExitCode(0);
        $this->assertSame($original, Role::findByName('BranchAdmin')->permissions->pluck('name')->all());
        $this->artisan('branch:roles', ['--role' => ['BranchAdmin'], '--apply' => true])->assertExitCode(0);
        $role = Role::findByName('BranchAdmin')->fresh();
        $this->assertTrue($role->hasPermissionTo('user-create'));
        $this->assertTrue($role->hasPermissionTo('role-create'));
        $this->assertFalse($role->permissions->contains('name', 'all-centre'));
        $this->assertNull($role->branch_id);
    }

    public function test_transfer_search_hides_central_accounts_and_finds_branch_staff_by_name(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $teacher = $this->actor('Teacher');
        $teacher->update(['name' => 'Find Teacher']);
        $this->actingAs($owner)->getJson('/branch-transfers/targets?type=user&q=Find')
            ->assertOk()->assertJsonPath('data.0.id', $teacher->id);
        $this->getJson('/branch-transfers/targets?type=user&q='.urlencode($owner->name))->assertOk()->assertJsonCount(0, 'data');
        $this->get('/branch-transfers')->assertOk()->assertSee('find-transfer', false);
    }


    public function test_legacy_student_and_export_bulk_requests_reject_mixed_branches_without_deleting(): void
    {
        $a = Contact::create(['centre' => $this->a->id]);
        $b = Contact::create(['centre' => $this->b->id]);
        $actor = $this->actor(permissions: ['student-delete', 'student-export']);
        $this->actingAs($actor)->postJson('/contacts/destroy_all', ['table_records' => [$a->id, $b->id]])->assertForbidden();
        $this->postJson('/contact/export', ['table_records' => [$a->id, $b->id]])->assertForbidden();
        $this->assertDatabaseHas('contacts', ['id' => $a->id]);
        $this->assertDatabaseHas('contacts', ['id' => $b->id]);
    }

    public function test_inactive_or_module_disabled_branches_are_excluded_from_public_directory(): void
    {
        $this->a->update(['enabled_modules' => ['crm']]);
        $this->b->update(['enabled_modules' => ['students']]);
        $this->getJson('/api/public/branches')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $this->a->id);
        $this->a->update(['status' => 0]);
        $this->getJson('/api/public/branches')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_staff_api_blocks_guest_and_cannot_read_other_branch(): void
    {
        $this->getJson('/api/searchstudent?centre='.$this->a->id)->assertUnauthorized();
        $actor = $this->actor(permissions: ['student-list']);
        $this->withToken($actor->createToken('test')->plainTextToken)
            ->getJson('/api/searchstudent?centre='.$this->b->id)->assertForbidden();
    }


    public function test_restarting_students_in_bulk_rejects_a_foreign_student_before_any_change(): void
    {
        $a = Contact::create(['centre' => $this->a->id, 'discontinued' => 1]);
        $b = Contact::create(['centre' => $this->b->id, 'discontinued' => 1]);
        $actor = $this->actor(permissions: ['student-edit']);
        $this->actingAs($actor)->postJson('/discontinued/destroy_all', ['table_records' => [$a->id, $b->id]])->assertForbidden();
        $this->assertEquals(1, $a->fresh()->discontinued);
        $this->assertEquals(1, $b->fresh()->discontinued);
    }

    public function test_old_lesson_images_remain_with_the_issuing_branch_after_student_moves(): void
    {
        $student = Contact::create(['centre' => $this->b->id]);
        $history = \App\Models\Histrories::create(['centre' => $this->a->id, 'student_id' => $student->id, 'teacher_id' => 0, 'level_id' => 0, 'term' => '1', 'bookuse' => '1', 'course_remaining' => '9', 'date' => now()->toDateString(), 'stime' => '09:00:00', 'etime' => '10:00:00', 'start_date' => now()->toDateString(), 'comment' => '', 'signature' => '']);
        $image = \App\Models\HistoryStudentImg::forceCreate(['history_id' => $history->id, 'student_id' => $student->id, 'img' => 'historical.png']);
        $context = app(BranchContext::class);
        $a = $this->actor();
        $b = $this->actor(branch: $this->b);
        $this->assertTrue($context->runAs($a, fn () => \App\Models\HistoryStudentImg::whereKey($image->id)->exists()));
        $this->assertFalse($context->runAs($b, fn () => \App\Models\HistoryStudentImg::whereKey($image->id)->exists()));
    }

    public function test_branch_cannot_detach_another_branches_child_from_shared_parent_account(): void
    {
        $own = Contact::create(['centre' => $this->a->id]);
        $other = Contact::create(['centre' => $this->b->id]);
        $parent = Parents::create(['centre_id' => $this->a->id, 'student_id' => $own->id.','.$other->id, 'fname' => 'Shared', 'lname' => 'Parent', 'email' => 'shared@example.test']);
        try {
            app(BranchContext::class)->runAs($this->actor(), fn () => $parent->update(['student_id' => (string) $own->id]));
            $this->fail('A branch must not modify shared parent membership.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('parent_student', ['parent_id' => $parent->id, 'student_id' => $other->id]);
    }

    public function test_book_shipment_access_rejects_other_destination_and_unprivileged_hq_staff(): void
    {
        $creator = $this->actor('SystemAdmin', $this->hq);
        $shipment = \App\Models\BookShipment::create(['shipment_number' => 'TEST-SHIP-1', 'destination_centre_id' => $this->a->id,
            'destination_centre_code' => 'AA', 'destination_centre_name' => 'A', 'status' => 'DISPATCHED',
            'created_by' => $creator->id, 'created_by_name' => 'Owner', 'dispatched_at' => now()]);
        $other = $this->actor(branch: $this->b, permissions: ['book-shipment-receive']);
        $this->actingAs($other)->getJson('/incoming-book-shipments/'.$shipment->id)->assertNotFound();
        $teacher = $this->actor('Teacher', $this->hq, ['book-shipment-list', 'all-centre']);
        $this->actingAs($teacher)->getJson('/book-shipments')->assertForbidden();
    }


    public function test_hq_teacher_cannot_select_another_branch_or_read_its_financial_totals(): void
    {
        config(['financial-documents.credit_note.enabled' => true]);
        $actor = $this->actor('Teacher', $this->hq, ['dashboard', 'receipt-list', 'all-centre']);
        \App\Models\Receipt::create(['centre' => $this->b->id, 'receipt_number' => 'OTHER-1',
            'total_fee' => '900.00', 'payment_date' => now()->toDateString(), 'status' => 1]);
        $this->actingAs($actor)->postJson('/receipt_sum_by_date', [
            'dcentre' => $this->b->id, 'date_range' => now()->toDateString().' - '.now()->toDateString(),
        ])->assertOk()->assertJsonPath('net_revenue', '0.00');
        $this->withToken($actor->createToken('revenue-test')->plainTextToken)
            ->getJson('/api/revenue-summary-by-branch')->assertOk()->assertJsonPath('data.net_revenue', '0.00');
        $totals = app(BranchContext::class)->runAs($actor, fn () => app(\App\Domain\CreditNote\Queries\CreditNoteRevenueQuery::class)
            ->Totals(now()->toDateString(), now()->toDateString(), $this->b->id));
        $this->assertSame(0, $totals['gross_minor']);
    }

    public function test_account_without_a_branch_cannot_read_branch_data(): void
    {
        $actor = $this->actor(permissions: ['user-list']);
        $actor->department_id = null;
        $actor->save();
        $this->actingAs($actor)->getJson('/users')->assertForbidden();
    }

    public function test_every_application_route_targets_an_existing_method(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getActionName();
            if (!str_starts_with($action, 'App\\') || !str_contains($action, '@')) { continue; }
            [$class, $method] = explode('@', $action, 2);
            $this->assertTrue(method_exists($class, $method), $action.' at '.$route->uri());
        }
    }
}
