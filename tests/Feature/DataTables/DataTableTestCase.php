<?php

namespace Tests\Feature\DataTables;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Http, Mail, Notification};
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

abstract class DataTableTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Department $hq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        foreach (['role-list', 'role-edit', 'role-delete', 'user-list', 'centre-manage', 'department-list',
            'student-list', 'parent-list', 'history-list', 'order-list', 'receipt-list', 'invoice-list',
            'product-list', 'course-manage', 'affiliate-course-application-list', 'quiz-manage', 'quiz-list',
            'central-settings-manage', 'financial-settings-manage', 'integration-settings-manage', 'blog-list',
            'all-centre', 'centre-create'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $this->owner = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'department_id' => 1, 'disable' => 0, 'language_code' => 'en']);
        $this->owner->assignRole(Role::findOrCreate('SystemAdmin', 'web'));
        $this->actingAs($this->owner);
        $this->withoutExceptionHandling();
    }

    protected static function profiles(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../../Fixtures/datatables.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function profile(string $key): array
    {
        return collect(self::profiles())->firstWhere('key', $key);
    }

    protected function table(string $key, array $overrides = []): array
    {
        $profile = $this->profile($key);
        $columns = array_map(fn ($column) => $column + ['search' => ['value' => '', 'regex' => false]], $profile['columns']);
        $payload = array_replace([
            'draw' => 7, 'start' => 0, 'length' => 10, 'columns' => $columns,
            'search' => ['value' => '', 'regex' => false],
        ], $overrides);
        // jQuery encodes boolean query parameters as true/false, not PHP's 1/0.
        array_walk_recursive($payload, function (&$value) {
            if (is_bool($value)) { $value = $value ? 'true' : 'false'; }
        });
        // Yajra captures the request in a singleton; each simulated browser request must be fresh.
        $this->app->forgetInstance('datatables.request');
        $response = $this->getJson($profile['uri'].'?'.http_build_query($payload), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk()->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data'])->assertJsonPath('draw', 7);
        $json = $response->json();
        $this->assertArrayNotHasKey('error', $json, $key.' '.json_encode($overrides).' '.($json['error'] ?? ''));
        $this->assertGreaterThanOrEqual(count($json['data']), $json['recordsFiltered']);
        $this->assertLessThanOrEqual($json['recordsTotal'], $json['recordsFiltered']);
        foreach ($json['data'] as $row) {
            foreach ($columns as $column) {
                $this->assertArrayHasKey($column['data'], $row, $key.': missing '.$column['data']);
            }
        }
        return $json;
    }

    protected function sort(string $key, string $column, string $direction, array $extra = []): array
    {
        $index = array_search($column, array_column($this->profile($key)['columns'], 'data'), true);
        $this->assertNotFalse($index);
        return $this->table($key, $extra + ['order' => [['column' => $index, 'dir' => $direction]]]);
    }

    protected function assertOrder(string $key, string $column, array $ids, array $extra = []): void
    {
        foreach (['asc' => $ids, 'desc' => array_reverse($ids)] as $direction => $expected) {
            $result = $this->sort($key, $column, $direction, $extra);
            $this->assertSame($expected, array_column($result['data'], 'id'), "$key.$column.$direction");
        }
    }

    protected function search(string $key, string $value, ?string $column = null): array
    {
        if ($column === null) {
            return $this->table($key, ['search' => ['value' => $value, 'regex' => false]]);
        }
        $columns = $this->profile($key)['columns'];
        $index = array_search($column, array_column($columns, 'data'), true);
        $columns[$index]['search'] = ['value' => $value, 'regex' => false];
        return $this->table($key, ['columns' => $columns]);
    }
}
