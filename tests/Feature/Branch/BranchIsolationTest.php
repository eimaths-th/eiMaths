<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Parents;
use App\Models\Position;
use App\Models\Product;
use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Department $hq;
    protected Department $a;
    protected Department $b;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::create(['id' => 1, 'name' => 'HQ test', 'code' => 'HQ', 'status' => 1]);
        $this->a = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $this->b = Department::create(['name' => 'Branch B', 'code' => 'BB', 'status' => 1]);
    }

    protected function actor(string $role = 'BranchAdmin', ?Department $branch = null, array $permissions = []): User
    {
        $actor = User::factory()->create(['department_id' => ($branch ?? $this->a)->id, 'disable' => 0, 'password' => bcrypt('branch-test-password')]);
        $actor->assignRole(Role::findOrCreate($role, 'web'));
        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        return $actor;
    }

    public function test_branch_all_centre_permission_does_not_override_branch_membership(): void
    {
        $actor = $this->actor(permissions: ['all-centre', 'user-list']);
        $this->actor('Teacher', $this->b);
        $this->assertFalse(BranchAccess::canCrossBranches($actor));
        $this->actingAs($actor)->getJson('/users', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 1);
    }

    public function test_central_admin_cannot_find_owner_in_user_list_or_role_options(): void
    {
        $admin = $this->actor('Admin', $this->hq, ['all-centre', 'user-list']);
        $owner = $this->actor('SystemAdmin', $this->hq);
        $this->actor('Teacher', $this->b);
        $response = $this->actingAs($admin)->getJson('/users', ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk()->assertJsonPath('recordsTotal', 2);
        $this->assertNotContains($owner->id, array_column($response->json('data'), 'id'));
        $this->assertFalse(BranchAccess::visibleRoles($admin)->where('name', 'SystemAdmin')->exists());
    }

    public function test_admin_cannot_update_owner_by_id(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $admin = $this->actor('Admin', $this->hq, ['all-centre', 'user-edit']);
        $this->actingAs($admin)->putJson('/users/save/'.$owner->id, [])->assertNotFound();
        $this->assertTrue($owner->fresh()->hasRole('SystemAdmin'));
    }

    public function test_branch_cannot_create_hq_or_assign_owner(): void
    {
        $actor = $this->actor(permissions: ['user-create']);
        $this->actingAs($actor)->postJson('/users', [
            'name' => 'No escalation', 'email' => 'escalation@example.test',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
            'department_id' => 1, 'role' => 'SystemAdmin',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'escalation@example.test']);
    }

    public function test_branch_cannot_mix_cross_branch_users_in_bulk_delete(): void
    {
        $actor = $this->actor(permissions: ['user-delete']);
        $own = $this->actor('Teacher');
        $other = $this->actor('Teacher', $this->b);
        $this->actingAs($actor)->postJson('/users/destroy_all', ['table_records' => [$own->id, $other->id]])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $own->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_shared_role_cannot_be_edited_by_branch(): void
    {
        $actor = $this->actor(permissions: ['role-edit']);
        $role = Role::findByName('Teacher');
        $this->actingAs($actor)->putJson('/roles/save/'.$role->id, ['name' => 'Changed', 'permission' => []])->assertForbidden();
        $this->assertSame('Teacher', $role->fresh()->name);
    }

    public function test_owner_last_account_cannot_be_deleted(): void
    {
        Permission::findOrCreate('user-delete', 'web');
        $owner = $this->actor('SystemAdmin', $this->hq);
        $this->actingAs($owner)->deleteJson('/users/destroy', ['id' => $owner->id])->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_can_delete_another_owner_when_one_remains(): void
    {
        Permission::findOrCreate('user-delete', 'web');
        $owner = $this->actor('SystemAdmin', $this->hq);
        $other = $this->actor('SystemAdmin', $this->hq);
        $this->actingAs($owner)->deleteJson('/users/destroy', ['id' => $other->id])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_disabled_module_blocks_endpoint_even_with_permission(): void
    {
        $actor = $this->actor(permissions: ['user-list']);
        DB::table('departments')->where('id', $this->a->id)->update(['enabled_modules' => json_encode(['students'])]);
        $this->actingAs($actor)->getJson('/users')->assertForbidden();
    }

    public function test_archived_branch_cannot_use_existing_session(): void
    {
        $actor = $this->actor(permissions: ['user-list']);
        $this->a->update(['status' => 0]);
        $this->actingAs($actor)->getJson('/users')->assertForbidden();
    }

    public function test_scopes_apply_to_students_parent_and_stock_queries_and_reset_between_actors(): void
    {
        $a = $this->actor();
        $b = $this->actor('BranchAdmin', $this->b);
        $studentA = Contact::create(['centre' => $this->a->id, 'name' => 'Student A', 'type' => 'student']);
        $studentB = Contact::create(['centre' => $this->b->id, 'name' => 'Student B', 'type' => 'student']);
        $context = app(BranchContext::class);
        $this->assertSame([$studentA->id], $context->runAs($a, fn () => Contact::pluck('id')->all()));
        $this->assertSame([$studentB->id], $context->runAs($b, fn () => Contact::pluck('id')->all()));
        $this->assertNull($context->actor);
    }

    public function test_parent_token_cannot_select_another_parent_or_student(): void
    {
        $own = Contact::create(['centre' => $this->a->id, 'name' => 'Own student', 'type' => 'student']);
        $other = Contact::create(['centre' => $this->b->id, 'name' => 'Other student', 'type' => 'student']);
        $parent = Parents::create(['student_id' => (string) $own->id, 'centre_id' => $this->a->id, 'fname' => 'Parent', 'lname' => 'Test', 'email' => 'parent@example.test']);
        $token = $parent->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/parent/get-student', ['parent_id' => $parent->id, 'student_id' => $other->id])->assertForbidden();
    }

    public function test_parent_can_read_own_child_without_exposing_student_password(): void
    {
        $student = Contact::create(['centre' => $this->a->id, 'name' => 'Own child', 'type' => 'student', 'password' => bcrypt('private-password')]);
        $parent = Parents::create(['student_id' => (string) $student->id, 'centre_id' => $this->a->id, 'fname' => 'Parent', 'lname' => 'Test', 'email' => 'own@example.test']);
        $response = $this->withToken($parent->createToken('test')->plainTextToken)->postJson('/api/parent/get-student', ['student_id' => $student->id]);
        $response->assertOk()->assertJsonPath('data.students.0.id', $student->id);
        $this->assertArrayNotHasKey('password', $response->json('data.students.0'));
    }

    public function test_position_must_match_new_users_branch(): void
    {
        $actor = $this->actor(permissions: ['user-create']);
        $position = Position::create(['department_id' => $this->b->id, 'name' => 'Other department', 'status' => 1]);
        $this->actingAs($actor)->postJson('/users', [
            'name' => 'Wrong position', 'email' => 'position@example.test', 'department_id' => $this->a->id,
            'position_id' => $position->id, 'role' => 'Teacher',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertUnprocessable();
        $this->assertDatabaseMissing('users', ['email' => 'position@example.test']);
    }

    public function test_owner_can_create_branch_with_explicit_modules_and_archive_without_deleting(): void
    {
        foreach (['centre-create', 'centre-delete'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $owner = $this->actor('SystemAdmin', $this->hq);
        $response = $this->actingAs($owner)->postJson('/departments/store', [
            'name' => 'New branch', 'code' => 'NC', 'status' => 1, 'enabled_modules' => ['students', 'users'],
        ])->assertOk();
        $id = $response->json('data.id');
        $this->assertSame(['students', 'users'], Department::findOrFail($id)->enabled_modules);
        $this->deleteJson('/departments/destroy', ['id' => $id])->assertOk();
        $this->assertDatabaseHas('departments', ['id' => $id, 'status' => 0]);
    }

    public function test_hq_can_delegate_narrow_affiliate_finance_without_cross_branch_user_access(): void
    {
        $finance = $this->actor('AffiliateFinance', $this->hq, ['affiliate-withdrawal-pay', 'all-centre']);
        $branch = $this->actor('AffiliateFinance', $this->a, ['affiliate-withdrawal-pay', 'all-centre']);
        $this->assertTrue($finance->can('affiliate-withdrawal-pay'));
        $this->assertFalse(BranchAccess::canCrossBranches($finance));
        $this->assertFalse($finance->can('all-centre'));
        $this->assertFalse($branch->can('affiliate-withdrawal-pay'));
        $this->assertFalse(BranchAccess::canCrossBranches($branch));
    }

    public function test_user_table_headers_match_data_and_related_columns_can_be_sorted(): void
    {
        $position = Position::create([
            'department_id' => $this->a->id,
            'name' => 'Academic',
            'status' => 1,
        ]);
        $actor = $this->actor(permissions: ['user-list']);
        $actor->update(['position_id' => $position->id]);

        $page = $this->actingAs($actor)->get('/users');
        $page->assertOk()
            ->assertSeeInOrder(['User Name', 'Email', 'Centre', 'Department', 'Role', 'Disable', 'Action'])
            ->assertDontSee("data: 'referral'", false);

        $names = ['checkbox', 'name', 'email', 'department', 'position', 'role', 'disable', 'action'];
        $columns = array_map(static fn (string $name): array => [
            'data' => $name,
            'name' => $name,
            'searchable' => 'true',
            'orderable' => 'true',
            'search' => ['value' => '', 'regex' => 'false'],
        ], $names);

        foreach ([3, 4, 5] as $column) {
            $query = http_build_query([
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => $columns,
                'order' => [['column' => $column, 'dir' => 'asc']],
                'search' => ['value' => '', 'regex' => 'false'],
            ]);
            $this->actingAs($actor)
                ->getJson('/users?'.$query, ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()
                ->assertJsonPath('data.0.name', $actor->name)
                ->assertJsonPath('data.0.department', $this->a->name)
                ->assertJsonPath('data.0.position', $position->name)
                ->assertJsonPath('data.0.role', 'BranchAdmin');
        }
    }

    public function test_role_form_groups_permissions_and_keeps_individual_inputs(): void
    {
        foreach ([
            'role-list', 'student-list', 'parent-create', 'receipt-list',
            'credit-note-show', 'affiliate-list', 'central-settings-manage',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $owner = $this->actor('SystemAdmin', $this->hq);
        $owner->update(['language_code' => 'th']);

        $this->actingAs($owner)->get('/roles')
            ->assertOk()
            ->assertSee('นักเรียนและผู้ปกครอง')
            ->assertSee('การเงินและเอกสาร')
            ->assertSee('Affiliate')
            ->assertSee('การตั้งค่าระบบ')
            ->assertSee('เลือกทั้งกลุ่ม')
            ->assertSee('เลือกทั้งหมด')
            ->assertSee('ล้างทั้งหมด')
            ->assertSee('data-permission-group="students"', false)
            ->assertSee('data-permission-group="finance"', false)
            ->assertSee('name="permission[]"', false)
            ->assertSee('name="epermission[]"', false)
            ->assertSee('student-list')
            ->assertSee('receipt-list');
    }
}
