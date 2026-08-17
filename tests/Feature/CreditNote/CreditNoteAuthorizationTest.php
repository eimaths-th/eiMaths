<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Queries\CreditNoteQuery;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'credit-note-list', 'credit-note-show', 'credit-note-create',
        'credit-note-edit', 'credit-note-issue', 'credit-note-cancel',
    ];

    #[Test]
    public function migration_creates_exact_web_permissions_without_granting_roles(): void
    {
        $this->assertSame(self::PERMISSIONS, Permission::query()->whereIn('name', self::PERMISSIONS)->orderBy('id')->pluck('name')->all());
        $this->assertSame(6, Permission::query()->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->count());
        $this->assertSame(0, DB::table('role_has_permissions')->whereIn('permission_id', Permission::whereIn('name', self::PERMISSIONS)->pluck('id'))->count());
    }

    #[Test]
    public function scoped_query_allows_same_centre_and_hides_cross_centre_and_null_department(): void
    {
        $first_centre = $this->CreateDepartment('AUTH-A');
        $second_centre = $this->CreateDepartment('AUTH-B');
        $same = $this->CreateActor($first_centre->id, ['credit-note-list', 'credit-note-show']);
        $cross = $this->CreateNote($second_centre->id);
        $same_note = $this->CreateNote($first_centre->id);
        $query = app(CreditNoteQuery::class);

        $this->assertSame($same_note->id, $query->FindVisibleOrFail($same, $same_note->id)->id);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $query->FindVisibleOrFail($same, $cross->id);
    }

    #[Test]
    public function all_centre_permission_sees_every_centre(): void
    {
        $first_centre = $this->CreateDepartment('AUTH-C');
        $second_centre = $this->CreateDepartment('AUTH-D');
        $actor = $this->CreateActor($first_centre->id, ['credit-note-list', 'credit-note-show', 'all-centre']);
        $first = $this->CreateNote($first_centre->id);
        $second = $this->CreateNote($second_centre->id);

        $ids = app(CreditNoteQuery::class)->ForList($actor)->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $ids);
    }

    #[Test]
    public function policy_requires_dedicated_permission_and_centre_scope(): void
    {
        $centre = $this->CreateDepartment('AUTH-E');
        $actor = $this->CreateActor($centre->id, []);
        $note = $this->CreateNote($centre->id);
        $this->assertFalse(Gate::forUser($actor)->allows('show', $note));

        $actor->givePermissionTo(Permission::findByName('credit-note-show', 'web'));
        $this->assertTrue(Gate::forUser($actor)->allows('show', $note));
    }

    private function CreateActor(?int $department_id, array $permissions): User
    {
        $actor = User::factory()->create(['department_id' => $department_id, 'password' => Hash::make('secret')]);
        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        return $actor;
    }

    private function CreateDepartment(string $code): Department
    {
        return Department::query()->create(['name' => $code, 'code' => $code, 'status' => 1]);
    }

    private function CreateNote(int $centre_id): CreditNote
    {
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre_id, 'receipt_number' => 'R-' . uniqid(), 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        return CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre_id, 'status' => 'draft', 'currency' => 'THB']);
    }
}
