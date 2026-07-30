<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CrmLeadsMenuPermissionTest extends TestCase
{
    public function test_permission_wraps_the_complete_crm_leads_menu_group(): void
    {
        $menu = file_get_contents($this->projectPath('resources/views/layouts/menu.blade.php'));
        $permissionStart = strpos($menu, "@can('crm-leads-menu-view')");

        $this->assertNotFalse($permissionStart);

        $permissionEnd = strpos($menu, '@endcan', $permissionStart);

        $this->assertNotFalse($permissionEnd);

        $guardedMenu = substr($menu, $permissionStart, $permissionEnd - $permissionStart);

        $this->assertStringContainsString("route('crm.leads.index')", $guardedMenu);
        $this->assertStringContainsString("route('tracking-links.index')", $guardedMenu);
        $this->assertStringContainsString("route('tracking-links.report')", $guardedMenu);
    }

    public function test_migration_creates_the_web_permission_and_backfills_existing_roles(): void
    {
        $migration = file_get_contents($this->projectPath(
            'database/migrations/2026_07_30_000000_add_crm_leads_menu_view_permission.php'
        ));

        $this->assertStringContainsString(
            "private const PERMISSION_NAME = 'crm-leads-menu-view';",
            $migration
        );
        $this->assertStringContainsString("'guard_name' => 'web'", $migration);
        $this->assertStringContainsString('if ($permission->wasRecentlyCreated)', $migration);
        $this->assertStringContainsString(
            "Role::where('guard_name', 'web')",
            $migration
        );
        $this->assertStringContainsString('givePermissionTo($permission)', $migration);
        $this->assertSame(4, substr_count($migration, 'forgetCachedPermissions()'));
    }

    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
