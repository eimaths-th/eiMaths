<?php

namespace Tests\Feature\Elearning;

use App\Models\User;
use App\Support\Branch\PermissionPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class QuizResultPermissionMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    #[Test]
    public function MapsOnlyEquivalentRoleCapabilities(): void
    {
        foreach (['quiz-manage', 'quiz-list', 'unrelated-capability'] as $permission_name) {
            Permission::findOrCreate($permission_name, 'web');
        }
        $cases = [
            'NoAuthority' => [[], []],
            'Admin' => [[], []],
            'ManageOnly' => [['quiz-manage'], ['quiz-results-list']],
            'ListOnly' => [['quiz-list'], []],
            'CustomReviewer' => [['quiz-manage', 'quiz-list'], ['quiz-results-list', 'quiz-results-view']],
        ];
        foreach ($cases as $role_name => [$legacy_permissions]) {
            Role::findOrCreate($role_name, 'web')->syncPermissions($legacy_permissions);
        }
        $migration = require database_path('migrations/2026_11_05_000001_register_quiz_result_permissions.php');
        $migration->up();
        foreach ($cases as $role_name => [$legacy_permissions, $expected_permissions]) {
            $actual_permissions = Role::findByName($role_name, 'web')->permissions->pluck('name')->filter(fn ($permission_name) => str_starts_with($permission_name, 'quiz-results-'))->sort()->values()->all();
            $this->assertSame($expected_permissions, $actual_permissions, $role_name);
        }
    }

    #[Test]
    public function PreservesExistingAssignmentsWhenRepeatedAndRolledBack(): void
    {
        Permission::findOrCreate('quiz-results-view', 'web');
        Permission::findOrCreate('unrelated-capability', 'web');
        $role = Role::findOrCreate('ManualGrant', 'web');
        $role->syncPermissions(['quiz-results-view', 'unrelated-capability']);
        $migration = require database_path('migrations/2026_11_05_000001_register_quiz_result_permissions.php');
        $migration->up();
        $migration->up();
        $migration->down();
        foreach (['quiz-results-list', 'quiz-results-view'] as $permission_name) {
            $this->assertSame(1, Permission::where('name', $permission_name)->where('guard_name', 'web')->count());
        }
        $this->assertSame(['quiz-results-view', 'unrelated-capability'], $role->fresh()->permissions->pluck('name')->sort()->values()->all());
    }

    #[Test]
    public function DoesNotConvertDirectUserPermissions(): void
    {
        Permission::findOrCreate('quiz-manage', 'web');
        Permission::findOrCreate('quiz-list', 'web');
        $actor = User::factory()->create(['password' => 'result-test-password']);
        $actor->givePermissionTo(['quiz-manage', 'quiz-list']);
        $migration = require database_path('migrations/2026_11_05_000001_register_quiz_result_permissions.php');
        $migration->up();
        $this->assertSame(['quiz-list', 'quiz-manage'], $actor->fresh()->permissions->pluck('name')->sort()->values()->all());
    }

    #[Test]
    public function ShowsLocalizedPermissionLabels(): void
    {
        foreach (['en' => ['View quiz results', 'View quiz answers'], 'th' => ['ดูผลสอบ', 'ดูคำตอบแบบทดสอบ']] as $locale => $labels) {
            app()->setLocale($locale);
            $this->assertSame($labels[0], PermissionPresenter::label('quiz-results-list'));
            $this->assertSame($labels[1], PermissionPresenter::label('quiz-results-view'));
        }
    }
}
