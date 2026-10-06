<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\Parents;
use App\Models\Position;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchEndpointAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_staff_cannot_read_or_mutate_other_branch_records_by_id(): void
    {
        [$actor, $own, $other] = $this->records();
        $this->actingAs($actor);

        foreach ([
            '/positions/edit/'.$other['position']->id,
            '/parent/'.$other['parent']->id.'/edit',
            '/histories/show/'.$other['history']->id,
            '/products/edit/'.$other['product']->id,
        ] as $url) {
            $this->assertDenied($this->getJson($url), $url);
        }

        foreach ([
            '/positions/save/'.$other['position']->id,
            '/parent/update/'.$other['parent']->id,
            '/histories/'.$other['history']->id,
            '/products/save/'.$other['product']->id,
        ] as $url) {
            $this->assertDenied($this->putJson($url, ['name' => 'Tampered']), $url);
        }

        $this->assertDenied($this->deleteJson('/positions/destroy', ['id' => $other['position']->id]), 'position delete');
        $this->assertDenied($this->deleteJson('/parent/'.$other['parent']->id), 'parent delete');
        $this->assertDenied($this->deleteJson('/histories/'.$other['history']->id), 'history delete');
        $this->assertDenied($this->deleteJson('/products/destroy', ['id' => $other['product']->id]), 'product delete');

        $this->assertDatabaseHas('positions', ['id' => $other['position']->id, 'name' => 'Other position']);
        $this->assertDatabaseHas('parents', ['id' => $other['parent']->id, 'fname' => 'Other']);
        $this->assertDatabaseHas('histrories', ['id' => $other['history']->id, 'comment' => 'Other history']);
        $this->assertDatabaseHas('products', ['id' => $other['product']->id, 'name' => 'Other product']);
    }

    public function test_mixed_branch_bulk_selection_is_rejected_atomically(): void
    {
        [$actor, $own, $other] = $this->records();
        $this->actingAs($actor);

        foreach ([
            ['/positions/destroy_all', $own['position']->id, $other['position']->id],
            ['/histories/destroy_all', $own['history']->id, $other['history']->id],
            ['/products/destroy_all', $own['product']->id, $other['product']->id],
        ] as [$url, $ownId, $otherId]) {
            $this->assertDenied($this->postJson($url, ['table_records' => [$ownId, $otherId]]), $url);
        }

        foreach (['position' => 'positions', 'history' => 'histrories', 'product' => 'products'] as $key => $table) {
            $this->assertDatabaseHas($table, ['id' => $own[$key]->id]);
            $this->assertDatabaseHas($table, ['id' => $other[$key]->id]);
        }
    }

    public function test_branch_staff_cannot_create_a_position_for_another_branch(): void
    {
        [$actor, , $other] = $this->records();
        $this->actingAs($actor);

        $this->assertDenied($this->postJson('/positions/store', [
            'name' => 'Cross branch position',
            'department_id' => $other['department']->id,
            'status' => 1,
        ]), 'position create');
        $this->assertDatabaseMissing('positions', ['name' => 'Cross branch position']);
    }

    public function test_branch_staff_cannot_request_other_branch_running_numbers_or_stock(): void
    {
        [$actor, , $other] = $this->records();
        $actor->givePermissionTo(Permission::findOrCreate('parent-create', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('product-create', 'web'));
        $this->actingAs($actor);

        foreach ([
            '/parent/running/'.$other['department']->id,
            '/products/running/'.$other['department']->id,
            '/products/stock?id='.$other['product']->id,
        ] as $url) {
            $this->assertDenied($this->getJson($url), $url);
        }
    }

    private function records(): array
    {
        $a = Department::create(['name' => 'A', 'code' => 'AA', 'status' => 1]);
        $b = Department::create(['name' => 'B', 'code' => 'BB', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $a->id, 'disable' => 0, 'password' => bcrypt('secret')]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        foreach ([
            'department-list', 'department-create', 'department-edit', 'department-delete',
            'parent-list', 'parent-edit', 'parent-delete',
            'history-list', 'history-show', 'history-edit', 'history-delete',
            'product-list', 'product-show', 'product-edit', 'product-delete',
        ] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $own = $this->branchRecords($a, 'Own');
        $other = $this->branchRecords($b, 'Other');

        return [$actor, $own, $other];
    }

    private function branchRecords(Department $department, string $name): array
    {
        $student = Contact::create(['centre' => $department->id, 'name' => $name.' student', 'type' => 'student']);
        return [
            'department' => $department,
            'position' => Position::create(['department_id' => $department->id, 'name' => $name.' position', 'status' => 1]),
            'parent' => Parents::create(['centre_id' => $department->id, 'student_id' => (string) $student->id,
                'fname' => $name, 'lname' => 'Parent', 'email' => strtolower($name).'@example.test']),
            'history' => Histrories::create(['centre' => $department->id, 'student_id' => $student->id,
                'teacher_id' => 0, 'level_id' => 1, 'term' => '1', 'bookuse' => '1', 'course_remaining' => '1',
                'date' => '2026-10-06', 'stime' => '10:00:00', 'etime' => '11:00:00',
                'start_date' => '2026-10-06', 'comment' => $name.' history', 'signature' => 'test']),
            'product' => Product::create(['centre' => $department->id, 'name' => $name.' product', 'amount' => 1]),
        ];
    }

    private function assertDenied($response, string $action): void
    {
        $this->assertContains($response->getStatusCode(), [403, 404], $action.' should not be accessible cross-branch');
    }
}
