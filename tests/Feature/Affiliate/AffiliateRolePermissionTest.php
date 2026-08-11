<?php

namespace Tests\Feature\Affiliate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function restriction_migration_removes_cms_permissions_from_affiliate_role_and_can_restore_them(): void
    {
        $role = Role::findOrCreate('Affiliate-user', 'web');
        $permission_names = [
            'affiliate-user',
            'blog-list',
            'blog-create',
            'blog-edit',
            'blog-delete',
            'crm-leads-menu-view',
        ];
        $permissions = collect($permission_names)
            ->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $role->syncPermissions($permissions);

        $migration = require base_path(
            'database/migrations/2026_08_07_000000_restrict_affiliate_user_permissions.php'
        );
        $migration->up();

        $this->assertSame(
            ['affiliate-user'],
            $role->fresh()->permissions()->pluck('name')->sort()->values()->all()
        );

        $migration->down();

        $this->assertSame(
            collect($permission_names)->sort()->values()->all(),
            $role->fresh()->permissions()->pluck('name')->sort()->values()->all()
        );
    }

    #[Test]
    public function affiliate_user_without_cms_permissions_does_not_see_cms_menu_entries(): void
    {
        $user = \App\Models\User::factory()->create(['password' => Hash::make('password')]);
        $affiliate_role = Role::findOrCreate('Affiliate-user', 'web');
        $affiliate_role->syncPermissions([
            Permission::findOrCreate('affiliate-user', 'web'),
        ]);
        $user->assignRole($affiliate_role);
        $user->givePermissionTo(Permission::findOrCreate('role-list', 'web'));

        $response = $this->actingAs($user)->get(route('roles.index'));

        $response->assertOk()
            ->assertDontSee('CRM Leads')
            ->assertDontSee('Blog Management');
    }

    #[Test]
    public function affiliate_content_manage_is_granted_only_to_existing_admin_roles(): void
    {
        $system_admin = Role::findOrCreate('SystemAdmin', 'web');
        $admin = Role::findOrCreate('Admin', 'web');
        $affiliate = Role::findOrCreate('Affiliate-user', 'web');
        $permission = Permission::findOrCreate('affiliate-content-manage', 'web');
        $system_admin->revokePermissionTo($permission);
        $admin->revokePermissionTo($permission);
        $affiliate->revokePermissionTo($permission);

        $migration = require base_path(
            'database/migrations/2026_08_11_000004_add_affiliate_content_manage_permission.php'
        );
        $migration->up();

        $this->assertTrue($system_admin->fresh()->hasPermissionTo('affiliate-content-manage'));
        $this->assertTrue($admin->fresh()->hasPermissionTo('affiliate-content-manage'));
        $this->assertFalse($affiliate->fresh()->hasPermissionTo('affiliate-content-manage'));
    }
}
