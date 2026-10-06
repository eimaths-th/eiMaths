<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\Contact;
use App\Models\EiForm;
use App\Models\Product;
use App\Models\User;
use App\Support\Branch\BranchAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchDataAccessRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $branch_a;
    private Department $branch_b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch_a = Department::create(['name' => 'A', 'code' => 'AA', 'status' => 1]);
        $this->branch_b = Department::create(['name' => 'B', 'code' => 'BB', 'status' => 1]);
    }

    private function Actor(string $role_name, Department $branch, array $permissions = []): User
    {
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('branch-test-password')]);
        $actor->assignRole(Role::findOrCreate($role_name, 'web'));
        foreach ($permissions as $permission_name) {
            $actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $actor;
    }

    #[Test]
    public function NonHqSystemAdminCannotSearchAnotherBranch(): void
    {
        $actor = $this->Actor('SystemAdmin', $this->branch_a, ['student-list', 'all-centre']);
        Contact::create(['centre' => $this->branch_b->id, 'name' => 'Other student', 'type' => 'student']);

        $this->actingAs($actor)->getJson('/contacts/get-by-centre')
            ->assertOk()->assertJsonPath('total', 0);
        $this->assertFalse(BranchAccess::isOwner($actor));
        $this->assertFalse(BranchAccess::canCrossBranches($actor));
    }

    #[Test]
    public function BranchStaffCannotReadTheGlobalApplicationPool(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['student-list']);
        EiForm::create(['first_name_parent' => 'Application parent', 'email_parent' => 'parent@example.test']);

        $this->actingAs($actor)->getJson('/ei_form', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertForbidden();
        $this->get('/ei_form')->assertForbidden();
    }

    #[Test]
    public function CentralApplicationAccessRequiresStudentPermission(): void
    {
        $actor = $this->Actor('Admin', $this->hq);
        EiForm::create(['first_name_parent' => 'Application parent']);

        $this->actingAs($actor)->getJson('/ei_form', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertForbidden();
        $this->get('/ei_form')->assertForbidden();
    }

    #[Test]
    public function BranchMenuDoesNotOfferTheCentralApplicationPool(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['student-list']);

        $this->actingAs($actor)->get('/contacts')->assertOk()
            ->assertDontSee(route('ei_form.backEndIndex'), false);
    }

    #[Test]
    public function StockQueryCannotExposeForeignProductsThroughLegacyLedgerRows(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['product-list']);
        $foreign_product = Product::create(['centre' => $this->branch_b->id, 'name' => 'Foreign product', 'amount' => 8]);
        DB::table('stocks')->insert([
            'product_id' => $foreign_product->id, 'centre' => $this->branch_a->id,
            'user_id' => $actor->id, 'add_stock' => 8, 'in_stock' => 8,
        ]);

        $this->actingAs($actor)->getJson('/products/stock?id='.$foreign_product->id)
            ->assertForbidden();
    }

    #[Test]
    public function CentralStockReadExcludesMismatchedLedgerBranches(): void
    {
        $actor = $this->Actor('Admin', $this->hq, ['product-list', 'all-centre']);
        $product = Product::create(['centre' => $this->branch_a->id, 'name' => 'Own product', 'amount' => 8]);
        $valid_ledger_id = DB::table('stocks')->insertGetId([
            'product_id' => $product->id, 'centre' => $this->branch_a->id,
            'user_id' => $actor->id, 'add_stock' => 8, 'in_stock' => 8,
        ]);
        DB::table('stocks')->insert([
            'product_id' => $product->id, 'centre' => $this->branch_b->id,
            'user_id' => $actor->id, 'add_stock' => 7, 'in_stock' => 7,
        ]);

        $this->actingAs($actor)->getJson('/products/stock?id='.$product->id)
            ->assertOk()->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $valid_ledger_id);
    }

    #[Test]
    public function StockReadRejectsNonScalarQueryIds(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['product-list']);
        $product = Product::create(['centre' => $this->branch_a->id, 'name' => 'Own product', 'amount' => 8]);

        $this->actingAs($actor)->getJson('/products/stock?id[]='.$product->id)
            ->assertUnprocessable()->assertJsonValidationErrors('id');
    }

    #[Test]
    public function ProductCodeLookupRejectsAmbiguousBranchCodes(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['product-list']);
        Product::create(['centre' => $this->branch_a->id, 'code' => 'DUPLICATE', 'name' => 'First product', 'amount' => 8]);
        Product::create(['centre' => $this->branch_a->id, 'code' => 'DUPLICATE', 'name' => 'Second product', 'amount' => 6]);

        $this->actingAs($actor)->getJson('/products/find/DUPLICATE')
            ->assertOk()->assertJsonPath('code', 'AMBIGUOUS_PRODUCT_CODE')
            ->assertJsonMissingPath('product');
    }

    #[Test]
    public function HqOwnerRetainsCrossBranchStudentAccess(): void
    {
        $actor = $this->Actor('SystemAdmin', $this->hq, ['student-list']);
        Contact::create(['centre' => $this->branch_b->id, 'name' => 'Other student', 'type' => 'student']);

        $this->actingAs($actor)->getJson('/contacts/get-by-centre')
            ->assertOk()->assertJsonPath('total', 1);
        $this->assertTrue(BranchAccess::isOwner($actor));
        $this->assertTrue(BranchAccess::canCrossBranches($actor));
    }

    #[Test]
    public function CentralStaffWithStudentPermissionCanReadApplications(): void
    {
        $actor = $this->Actor('Admin', $this->hq, ['student-list']);
        EiForm::create(['first_name_parent' => 'Application parent', 'email_parent' => 'parent@example.test']);

        $this->actingAs($actor)->getJson('/ei_form', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.email_parent', 'parent@example.test');
        $this->get('/ei_form')->assertOk();
    }

    #[Test]
    public function PublicApplicationSubmissionRemainsAvailable(): void
    {
        $this->postJson('/ei-form/store', [
            'first_name_parent' => 'Public parent', 'email_parent' => 'public-parent@example.test',
        ])->assertOk()->assertJsonStructure(['html']);

        $this->assertDatabaseHas('ei_form', ['email_parent' => 'public-parent@example.test']);
    }

    #[Test]
    public function CentralStockReadPreservesTransferDestination(): void
    {
        $actor = $this->Actor('Admin', $this->hq, ['product-list', 'all-centre']);
        $product = Product::create(['centre' => $this->branch_a->id, 'name' => 'Branch product', 'amount' => 8]);
        DB::table('stocks')->insert([
            'product_id' => $product->id, 'centre' => $this->branch_a->id,
            'user_id' => $actor->id, 'rm_stock' => 2, 'in_stock' => 8,
            'type' => 2, 'student_id' => $this->branch_b->id,
        ]);

        $this->actingAs($actor)->getJson('/products/stock?id='.$product->id)
            ->assertOk()->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.centre_name', 'A')
            ->assertJsonPath('data.0.student_or_centre_type', 'Centre')
            ->assertJsonPath('data.0.student_or_centre_name', 'B');
    }

    #[Test]
    public function ProductCodeLookupIgnoresInaccessibleDuplicateCodes(): void
    {
        $actor = $this->Actor('BranchAdmin', $this->branch_a, ['product-list']);
        $own_product = Product::create(['centre' => $this->branch_a->id, 'code' => 'SHARED', 'name' => 'Own product', 'amount' => 8]);
        Product::create(['centre' => $this->branch_b->id, 'code' => 'SHARED', 'name' => 'Other product', 'amount' => 6]);

        $this->actingAs($actor)->getJson('/products/find/SHARED')
            ->assertOk()->assertJsonPath('product.id', $own_product->id);
        $this->getJson('/products/find/MISSING')->assertOk()
            ->assertJsonStructure(['message'])->assertJsonMissingPath('product');
    }
}
