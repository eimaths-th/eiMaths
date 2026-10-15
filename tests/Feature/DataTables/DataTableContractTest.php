<?php

namespace Tests\Feature\DataTables;

use App\Models\{Department, Product, User};
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\{Permission, Role};

class DataTableContractTest extends DataTableTestCase
{
    public static function tables(): array
    {
        return array_combine(array_column(self::profiles(), 'key'), array_map(fn ($profile) => [$profile['key']], self::profiles()));
    }

    #[DataProvider('tables')]
    public function test_tables_accept_actual_columns_sort_search_pagination_and_no_matches(string $key): void
    {
        $extra = [];
        if ($key === 'stock-ledger') {
            $extra['id'] = Product::create(['name' => 'Empty stock', 'centre' => 1, 'code' => 'EMPTY', 'amount' => 0])->id;
        }
        $initial = $this->table($key, $extra);
        $this->table($key, $extra + ['start' => 10]);
        $this->table($key, $extra + ['length' => 25]);
        foreach ($this->profile($key)['columns'] as $column) {
            if ($column['orderable']) {
                foreach (['asc', 'desc'] as $direction) {
                    $this->sort($key, $column['data'], $direction, $extra);
                }
            }
        }
        $empty = $this->table($key, $extra + ['search' => ['value' => 'zz-no-match-datatable', 'regex' => false]]);
        $this->assertSame(0, $empty['recordsFiltered']);
        $this->assertSame([], $empty['data']);
        $this->assertSame($initial['recordsTotal'], $empty['recordsTotal']);
    }

    public function test_new_branch_tables_return_empty_data_without_exposing_other_branches(): void
    {
        $branch = Department::create(['name' => 'Empty branch', 'code' => 'EMPTY', 'status' => 1]);
        $actor = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'department_id' => $branch->id, 'disable' => 0, 'language_code' => 'en']);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $actor->givePermissionTo(Permission::all());
        $this->actingAs($actor);
        foreach (['students', 'graduated', 'discontinued', 'parents', 'histories', 'orders', 'receipts',
            'pending-receipts', 'pending-term-receipts', 'invoices', 'pending-invoices', 'products'] as $key) {
            $result = $this->table($key);
            $this->assertSame(0, $result['recordsTotal'], $key);
            $this->assertSame([], $result['data'], $key);
        }
    }
}
