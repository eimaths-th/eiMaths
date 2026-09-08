<?php

namespace Tests\Feature\Receipt;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PendingReceiptEmptyBranchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function EmptyBranchReceivesValidEmptyDataTablesResponse(): void
    {
        Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'HQ',
            'code' => 'HQ',
            'status' => 1,
            'branch_type' => 'hq',
        ]);
        $branch = Department::forceCreate([
            'id' => 5,
            'name' => 'New empty branch',
            'code' => 'EMPTY',
            'status' => 1,
            'enabled_modules' => null,
        ]);

        $role = Role::findOrCreate('BranchAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('receipt-list', 'web'));
        $user = User::factory()->create([
            'department_id' => $branch->id,
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->getJson(route('receipts_pending', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 0)
            ->assertJsonPath('recordsFiltered', 0)
            ->assertJsonPath('data', []);
    }
}
