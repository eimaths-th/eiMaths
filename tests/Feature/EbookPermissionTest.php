<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EbookPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ebook.enabled' => true, 'ebook.demo' => false]);
        Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch = Department::create(['name' => 'Branch A', 'code' => 'BRA', 'status' => 1,
            'enabled_modules' => ['courses', 'users', 'students']]);
    }

    private function staff(array $permissions = [], ?string $roleName = null, ?int $branchId = null): User
    {
        $user = User::factory()->create(['department_id' => $branchId ?? $this->branch->id,
            'language_code' => 'en', 'disable' => 0, 'password' => Hash::make('test-password')]);
        if ($roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions([]);
            $user->assignRole($role);
        }
        foreach ($permissions as $name) { $user->givePermissionTo(Permission::findOrCreate($name, 'web')); }
        return $user;
    }

    private function assertManagementDenied(): void
    {
        foreach (['library', 'import', 'history'] as $section) {
            $this->get('/ebooks?section='.$section)->assertForbidden();
        }
        $this->postJson('/ebooks/import')->assertForbidden();
        $this->post('/ebooks/versions/1/publish')->assertForbidden();
        $this->get('/ebooks/versions/1/preview')->assertForbidden();
        $this->get('/ebooks/versions/1/image')->assertForbidden();
    }

    public function test_legacy_permissions_alone_no_longer_authorize_the_menu_or_direct_urls(): void
    {
        $this->actingAs($this->staff(['course-manage', 'student-list'], 'Admin', Department::HQ_ID));
        $this->get('/ebooks')->assertForbidden();
        $this->get('/ebooks?section=activity')->assertForbidden();
        $this->assertManagementDenied();
        $menu = view('layouts.menu', ['book_shipment_alert_role' => null])->render();
        $this->assertStringNotContainsString(route('ebooks.index'), $menu);
    }

    public function test_log_only_staff_see_the_nested_menu_and_only_their_branch_events(): void
    {
        foreach ([$this->branch->id => 'OWN-STUDENT', Department::HQ_ID => 'OTHER-STUDENT'] as $centre => $code) {
            $studentId = DB::table('contacts')->insertGetId(['centre' => $centre, 'code' => $code, 'name' => $code]);
            DB::table('ebook_events')->insert(['student_id' => $studentId, 'event_id' => Str::uuid(),
                'action' => 'book_open', 'source' => 'client', 'occurred_at' => now(), 'received_at' => now()]);
        }
        $this->actingAs($this->staff(['ebook-activity-list']));
        $response = $this->get('/ebooks')->assertOk()->assertViewHas('section', 'activity')
            ->assertSee('OWN-STUDENT')->assertDontSee('OTHER-STUDENT')
            ->assertDontSee('section=import', false)->assertDontSee('section=history', false);
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $ebookLinks = $xpath->query('//a[@href="'.route('ebooks.index').'"]');
        $this->assertSame(1, $ebookLinks->length);
        $parent = $ebookLinks->item(0)->parentNode->parentNode;
        $this->assertStringContainsString('nav-treeview', $parent->getAttribute('class'));
        $this->assertStringContainsString('Course Management', $parent->parentNode->textContent);
        $this->assertStringContainsString('menu-open', $parent->parentNode->getAttribute('class'));
        $this->assertStringContainsString('active', $ebookLinks->item(0)->getAttribute('class'));
        $this->assertManagementDenied();
    }

    public function test_managers_can_use_the_library_and_publish_but_activity_requires_its_own_permission(): void
    {
        $book = DB::table('ebook_books')->insertGetId(['code' => 'G11', 'title' => 'Book G11']);
        $version = DB::table('ebook_versions')->insertGetId(['book_id' => $book, 'number' => 1,
            'status' => 'ready', 'page_count' => 1, 'source_path' => 'private.pdf']);
        $this->actingAs($this->staff(['ebook-manage'], 'Admin', Department::HQ_ID));
        foreach (['library', 'import', 'history'] as $section) {
            $this->get('/ebooks?section='.$section)->assertOk()->assertViewHas('events', null)
                ->assertDontSee('section=activity', false);
        }
        $this->get('/ebooks/versions/'.$version.'/preview')->assertOk();
        $this->postJson('/ebooks/import')->assertUnprocessable()->assertJsonValidationErrors(['pdf']);
        $this->post('/ebooks/versions/'.$version.'/publish')->assertRedirect();
        $this->assertDatabaseHas('ebook_books', ['id' => $book, 'published_version_id' => $version]);
        $this->get('/ebooks?section=activity')->assertForbidden();
    }

    public function test_branch_users_cannot_use_central_management_and_modules_and_feature_flags_are_enforced(): void
    {
        $user = $this->staff(['ebook-manage', 'ebook-activity-list']);
        $this->actingAs($user);
        $this->get('/ebooks')->assertOk()->assertViewHas('section', 'activity');
        $this->assertManagementDenied();
        $this->branch->update(['enabled_modules' => ['users', 'students']]);
        $this->get('/ebooks')->assertForbidden();
        $this->assertStringNotContainsString(route('ebooks.index'), view('layouts.menu', ['book_shipment_alert_role' => null])->render());
        $this->branch->update(['enabled_modules' => ['courses']]);
        config(['ebook.enabled' => false]);
        $this->get('/ebooks')->assertNotFound();
        $this->postJson('/ebooks/import')->assertForbidden();
        $this->assertStringNotContainsString(route('ebooks.index'), view('layouts.menu', ['book_shipment_alert_role' => null])->render());
    }

    public function test_role_management_localizes_the_group_and_granting_or_revoking_access_takes_effect(): void
    {
        $owner = $this->staff(['role-list', 'role-edit'], 'SystemAdmin', Department::HQ_ID);
        $role = Role::create(['name' => 'ebook-reviewer', 'display_name' => 'E-book Reviewer',
            'branch_id' => $this->branch->id, 'guard_name' => 'web']);
        $reviewer = $this->staff();
        $reviewer->assignRole($role);
        foreach (['en' => ['Manage E-books (HQ)', 'View E-book Activity Log'],
            'th' => ['จัดการ E-book (ส่วนกลาง)', 'ดูประวัติการใช้งาน E-book']] as $locale => $labels) {
            $owner->update(['language_code' => $locale]);
            $this->actingAs($owner)->get(route('roles.index'))->assertOk()
                ->assertSee('data-permission-group="ebooks"', false)->assertSee($labels[0])->assertSee($labels[1]);
        }
        $permission = Permission::findByName('ebook-activity-list', 'web');
        $this->actingAs($owner)->putJson(route('roles.save', $role), ['name' => 'E-book Reviewer',
            'permission' => [$permission->id]])->assertOk();
        $this->actingAs($reviewer->fresh())->get('/ebooks')->assertOk();
        $this->actingAs($owner)->putJson(route('roles.save', $role), ['name' => 'E-book Reviewer',
            'permission' => []])->assertOk();
        $this->actingAs($reviewer->fresh())->get('/ebooks')->assertForbidden();
    }

    public function test_role_management_rejects_central_permissions_for_branches_and_unauthorized_delegation(): void
    {
        $owner = $this->staff(['role-create'], 'SystemAdmin', Department::HQ_ID);
        $manageId = Permission::findByName('ebook-manage', 'web')->id;
        $activityId = Permission::findByName('ebook-activity-list', 'web')->id;
        $this->actingAs($owner)->postJson(route('roles.store'), ['name' => 'Invalid Manager',
            'branch_id' => $this->branch->id, 'permission' => [$manageId]])->assertForbidden();
        $this->branch->update(['enabled_modules' => ['users']]);
        $this->postJson(route('roles.store'), ['name' => 'Disabled Module',
            'branch_id' => $this->branch->id, 'permission' => [$activityId]])->assertUnprocessable();
        $this->branch->update(['enabled_modules' => ['users', 'courses']]);
        $this->actingAs($this->staff(['role-create']))->postJson(route('roles.store'), ['name' => 'Unauthorized Grant',
            'branch_id' => $this->branch->id, 'permission' => [$activityId]])->assertForbidden();
        $this->assertDatabaseMissing('roles', ['display_name' => 'Invalid Manager']);
        $this->assertDatabaseMissing('roles', ['display_name' => 'Unauthorized Grant']);
    }

    public function test_permission_migration_preserves_only_equivalent_role_and_direct_assignments(): void
    {
        $course = Permission::findOrCreate('course-manage', 'web');
        $students = Permission::findOrCreate('student-list', 'web');
        $manager = Role::findOrCreate('Legacy Manager', 'web');
        $manager->givePermissionTo($course);
        $teacher = Role::findOrCreate('Legacy Teacher', 'web');
        $teacher->givePermissionTo($students);
        $unrelated = Role::findOrCreate('Unrelated Role', 'web');
        $directManager = $this->staff(['course-manage'], 'Admin', Department::HQ_ID);
        $directViewer = $this->staff(['student-list']);
        $unrelatedUser = $this->staff();
        $migration = require database_path('migrations/2026_10_09_000002_register_ebook_permissions.php');
        $migration->up();
        $migration->up(); // Safe retry: no duplicate assignments.
        foreach (['ebook-manage', 'ebook-activity-list'] as $name) {
            $this->assertTrue($manager->fresh()->hasPermissionTo($name));
            $this->assertTrue($directManager->fresh()->hasDirectPermission($name));
            $this->assertFalse($unrelated->fresh()->hasPermissionTo($name));
            $this->assertFalse($unrelatedUser->fresh()->hasDirectPermission($name));
        }
        $this->assertTrue($teacher->fresh()->hasPermissionTo('ebook-activity-list'));
        $this->assertFalse($teacher->fresh()->hasPermissionTo('ebook-manage'));
        $this->assertTrue($directViewer->fresh()->hasDirectPermission('ebook-activity-list'));
        $this->assertFalse($directViewer->fresh()->hasDirectPermission('ebook-manage'));
        $this->assertSame(3, $manager->fresh()->permissions()->count());
    }
}
