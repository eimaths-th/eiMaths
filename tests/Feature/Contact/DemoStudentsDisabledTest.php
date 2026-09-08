<?php

namespace Tests\Feature\Contact;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoStudentsDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('demo-students.enabled', false);

        Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'HQ',
            'code' => 'HQ',
            'status' => 1,
            'branch_type' => 'hq',
        ]);
        $branch = Department::forceCreate([
            'id' => 5,
            'name' => 'Branch',
            'code' => 'BRANCH',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('student-list', 'web'));
        $user = User::factory()->create([
            'department_id' => $branch->id,
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $user->assignRole($role);
        $this->actingAs($user);
    }

    #[Test]
    public function DemoStudentsMenuIsHidden(): void
    {
        $rendered = view('layouts.menu', ['book_shipment_alert_role' => null])->render();

        $this->assertStringNotContainsString('Demo Students', $rendered);
        $this->assertStringNotContainsString(route('demo.list'), $rendered);
    }

    #[Test]
    public function DemoStudentEndpointsAreUnavailableWithoutMutatingData(): void
    {
        $this->get(route('demo.list'))->assertNotFound();
        $this->get(route('demo.code'))->assertNotFound();
        $this->post(route('demo.store'), [
            'school' => 'Hidden',
            'name' => 'Hidden Demo',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertForbidden();

        $this->assertDatabaseMissing('contacts', ['type' => 'demo']);
    }
}
