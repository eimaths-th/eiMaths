<?php

namespace Tests\Feature\Affiliate;

use App\Models\Department;
use App\Models\CoursePending;
use App\Models\User;
use App\Support\Branch\PermissionPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateCourseApplicationPermissionTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PERMISSIONS = [
        'affiliate-course-application-list',
        'affiliate-course-application-create',
        'affiliate-course-application-edit',
        'affiliate-course-application-delete',
    ];

    private Department $branch;

    protected function setUp(): void
    {
        parent::setUp();
        Department::query()->updateOrCreate(
            ['id' => Department::HQ_ID],
            ['name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq'],
        );
        $this->branch = Department::query()->create([
            'name' => 'Branch A',
            'code' => 'BRA',
            'status' => 1,
        ]);
    }

    #[Test]
    public function PermissionsAreRegisteredInTheAffiliateGroupAndIncludedInAdministrativeTemplates(): void
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::NEW_PERMISSIONS)
            ->get();

        $this->assertCount(4, $permissions);
        $this->assertFalse(Permission::query()->where('name', 'course-pending-list')->exists());

        $affiliate_group = collect(PermissionPresenter::groups($permissions))->firstWhere('key', 'affiliate');
        $this->assertNotNull($affiliate_group);
        $this->assertEqualsCanonicalizing(
            self::NEW_PERMISSIONS,
            collect($affiliate_group['permissions'])->pluck('name')->all(),
        );
        $this->assertSame(
            'ดูรายการ: ใบสมัครคอร์สผ่าน Affiliate',
            collect($affiliate_group['permissions'])->firstWhere('name', 'affiliate-course-application-list')['label'],
        );

        foreach (self::NEW_PERMISSIONS as $permission) {
            $this->assertContains($permission, config('branch-role-templates.BranchAdmin'));
            $this->assertContains($permission, config('branch-role-templates.Admin'));
            $this->assertNotContains($permission, config('branch-role-templates.Teacher'));
            $this->assertFalse(Role::findOrCreate('Affiliate-user', 'web')->hasPermissionTo($permission));
        }
        foreach (['SystemAdmin', 'Admin', 'BranchAdmin'] as $role_name) {
            $role = Role::findByName($role_name, 'web');
            foreach (self::NEW_PERMISSIONS as $permission) {
                $this->assertTrue($role->hasPermissionTo($permission));
            }
        }
    }

    #[Test]
    public function MigrationPreservesLegacyRoleAndDirectUserAssignments(): void
    {
        $legacy_permission = Permission::findOrCreate('course-pending-list', 'web');
        $legacy_role = Role::findOrCreate('Legacy Course Applications', 'web');
        $legacy_role->givePermissionTo($legacy_permission);
        $legacy_user = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('password'),
        ]);
        $legacy_user->givePermissionTo($legacy_permission);

        $migration = require base_path(
            'database/migrations/2026_09_15_000001_move_course_application_permissions_to_affiliate.php'
        );
        $migration->up();

        $this->assertFalse(Permission::query()->where('name', 'course-pending-list')->exists());
        $this->assertTrue($legacy_role->fresh()->hasPermissionTo('affiliate-course-application-list'));
        $this->assertTrue($legacy_user->fresh()->hasDirectPermission('affiliate-course-application-list'));
    }

    #[Test]
    public function BranchStaffNeedTheNewListPermissionToOpenCourseApplications(): void
    {
        $allowed = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('password'),
        ]);
        $allowed->givePermissionTo(Permission::findByName('affiliate-course-application-list', 'web'));

        $this->actingAs($allowed)
            ->get(route('courses.pending.index'))
            ->assertOk()
            ->assertSee('Affiliate Course Applications')
            ->assertSee('Affiliate Course...');

        $legacy_only = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('password'),
        ]);
        $legacy_only->givePermissionTo(Permission::findOrCreate('course-pending-list', 'web'));

        $this->actingAs($legacy_only)
            ->get(route('courses.pending.index'))
            ->assertForbidden();
    }

    #[Test]
    public function UpdatingAnApplicationRequiresTheNewAffiliateEditPermission(): void
    {
        $pending = CoursePending::query()->create([
            'name' => 'Parent A',
            'email' => 'parent-a@example.test',
            'course_id' => 1,
            'department_id' => $this->branch->id,
            'status' => '1',
        ]);
        $actor = User::factory()->create([
            'department_id' => $this->branch->id,
            'disable' => 0,
            'password' => Hash::make('password'),
        ]);
        $actor->givePermissionTo(Permission::findByName('affiliate-course-application-list', 'web'));

        $this->actingAs($actor)
            ->post(route('courses.pending.confirm', $pending->id))
            ->assertForbidden();

        $actor->givePermissionTo(Permission::findByName('affiliate-course-application-edit', 'web'));
        $this->actingAs($actor)
            ->post(route('courses.pending.confirm', $pending->id))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame('2', $pending->fresh()->status);
    }
}
