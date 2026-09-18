<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAccessLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Department $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'User Access HQ',
            'code' => 'USER-ACCESS-HQ',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $this->branch = Department::create([
            'name' => 'User Access Branch',
            'code' => 'USERACCESS',
            'status' => 1,
            'enabled_modules' => null,
            'branch_type' => 'branch',
        ]);

        $system_admin = Role::findOrCreate('SystemAdmin', 'web');
        foreach (['user-list', 'user-create', 'user-edit', 'user-delete', 'role-list', 'role-create', 'role-edit', 'role-delete'] as $permission_name) {
            $system_admin->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        Role::findOrCreate('Teacher', 'web');

        $this->actor = User::factory()->create([
            'department_id' => Department::HQ_ID,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($system_admin);
    }

    public static function UserLabels(): array
    {
        return [
            'en' => ['en', ['User Management', 'User Name', 'Email', 'Centre', 'Department', 'Role', 'Disable', 'Action', 'Add User', 'Edit User']],
            'th' => ['th', ['จัดการผู้ใช้', 'ชื่อผู้ใช้', 'อีเมล', 'สาขา', 'แผนก', 'บทบาท', 'ปิดใช้งาน', 'การดำเนินการ', 'เพิ่มผู้ใช้', 'แก้ไขผู้ใช้']],
        ];
    }

    public static function RoleLabels(): array
    {
        return [
            'en' => ['en', ['Role Management', 'Role Name', 'Permission', 'Action', 'Add Role', 'Edit Role']],
            'th' => ['th', ['จัดการบทบาท', 'ชื่อบทบาท', 'สิทธิ์', 'การดำเนินการ', 'เพิ่มบทบาท', 'แก้ไขบทบาท']],
        ];
    }

    public static function PermissionLabels(): array
    {
        return [
            'en' => ['en', ['Permission Management', 'Permission Name', 'Action', 'Add Permission', 'Edit Permission']],
            'th' => ['th', ['จัดการสิทธิ์', 'ชื่อสิทธิ์', 'การดำเนินการ', 'เพิ่มสิทธิ์', 'แก้ไขสิทธิ์']],
        ];
    }

    #[Test]
    #[DataProvider('UserLabels')]
    public function UserPageRendersTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('users.index'));

        $response->assertOk()->assertSee('<html lang="' . $locale . '">', false);
        foreach ($labels as $label) {
            $response->assertSee($label);
        }
        $response->assertDontSee('user-access.');
    }

    #[Test]
    #[DataProvider('RoleLabels')]
    public function RolePageRendersTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('roles.index'));

        $response->assertOk()->assertSee('<html lang="' . $locale . '">', false);
        foreach ($labels as $label) {
            $response->assertSee($label);
        }
        $response->assertDontSee('user-access.');
    }

    #[Test]
    #[DataProvider('PermissionLabels')]
    public function PermissionPageRendersTheSelectedLocale(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('permission.index'));

        $response->assertOk()->assertSee('<html lang="' . $locale . '">', false);
        foreach ($labels as $label) {
            $response->assertSee($label);
        }
        $response->assertDontSee('user-access.');
    }

    #[Test]
    #[DataProvider('Locales')]
    public function UserRoleAndPermissionMessagesUseTheSelectedLocale(string $locale, array $messages): void
    {
        $this->actor->update(['language_code' => $locale]);
        $this->actingAs($this->actor, 'web');

        $user_response = $this->postJson(route('users.store'), [
            'name' => 'Locale Staff',
            'email' => 'locale-staff@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'department_id' => $this->branch->id,
            'position_id' => null,
            'role' => 'Teacher',
            'disable' => 0,
        ]);
        $user_response->assertOk()->assertJsonPath('success', $messages[0]);

        $role_response = $this->postJson(route('roles.store'), [
            'name' => 'Locale Reviewer',
            'branch_id' => $this->branch->id,
            'permission' => [],
        ]);
        $role_response->assertOk()->assertJsonPath('success', $messages[1]);

        $permission_response = $this->postJson(route('permission.store'), [
            'name' => 'locale-review',
        ]);
        $permission_response->assertOk()->assertJsonPath('success', $messages[2]);
    }

    public static function Locales(): array
    {
        return [
            'en' => ['en', ['User added successfully.', 'Role added successfully.', 'Permission added successfully.']],
            'th' => ['th', ['เพิ่มผู้ใช้สำเร็จ', 'เพิ่มบทบาทสำเร็จ', 'เพิ่มสิทธิ์สำเร็จ']],
        ];
    }
}
