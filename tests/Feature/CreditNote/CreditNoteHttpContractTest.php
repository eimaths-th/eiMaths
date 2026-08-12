<?php

namespace Tests\Feature\CreditNote;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteHttpContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
        config()->set('financial-documents.credit_note.financial_actions_enabled', true);
    }

    #[Test]
    public function index_returns_stable_json_and_preserves_valid_correlation_id(): void
    {
        $department = Department::query()->create(['name' => 'HTTP', 'code' => 'HTTP', 'status' => 1]);
        $actor = $this->CreateActor($department->id, ['credit-note-list']);

        $response = $this->actingAs($actor)->withHeaders([
            'Accept' => 'application/json',
            'X-Correlation-ID' => 'demo-http-1',
        ])->getJson('/credit-notes');

        $response->assertOk()->assertJsonPath('correlation_id', 'demo-http-1');
        $this->assertSame('demo-http-1', $response->headers->get('X-Correlation-ID'));
    }

    #[Test]
    public function invalid_correlation_is_replaced_and_missing_permission_is_forbidden(): void
    {
        $department = Department::query()->create(['name' => 'HTTP2', 'code' => 'HT2', 'status' => 1]);
        $actor = $this->CreateActor($department->id, []);

        $response = $this->actingAs($actor)->withHeaders([
            'Accept' => 'application/json',
            'X-Correlation-ID' => "bad value\n",
        ])->getJson('/credit-notes');

        $response->assertForbidden();
        $this->assertNotSame("bad value\n", $response->headers->get('X-Correlation-ID'));
    }

    #[Test]
    public function disabled_feature_returns_404_json_and_financial_routes_are_not_get_or_delete(): void
    {
        config()->set('financial-documents.credit_note.enabled', false);
        $department = Department::query()->create(['name' => 'HTTP3', 'code' => 'HT3', 'status' => 1]);
        $actor = $this->CreateActor($department->id, ['credit-note-list']);

        $response = $this->actingAs($actor)->getJson('/credit-notes');
        $response->assertNotFound()->assertJsonPath('error_code', 'CREDIT_NOTE_DISABLED');

        $this->assertNull(Route::getRoutes()->getByName('credit-notes.destroy'));
        $this->assertSame('POST', Route::getRoutes()->getByName('credit-notes.issue')->methods()[0]);
        $this->assertSame('POST', Route::getRoutes()->getByName('credit-notes.cancel')->methods()[0]);
    }

    private function CreateActor(int $department_id, array $permissions): User
    {
        $actor = User::factory()->create(['department_id' => $department_id, 'password' => Hash::make('secret')]);
        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        return $actor;
    }
}
