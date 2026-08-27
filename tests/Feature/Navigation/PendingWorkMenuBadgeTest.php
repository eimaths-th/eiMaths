<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PendingWorkMenuBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function AffiliateManagementDoesNotShowAffiliateAccountsMenu(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('course-manage', 'web'),
        ]);

        $response = $this->actingAs($admin)->get(route('affiliate.admin.index'));
        $menu = $response->getContent();

        $this->assertStringContainsString('Affiliate Management', $menu);
        $this->assertStringNotContainsString('Affiliate Accounts', $menu);
        $this->assertDoesNotMatchRegularExpression(
            '/<p>\s*Affiliate Management\s*<span[^>]*data-affiliate-pending-approval-badge/s',
            $menu,
        );
    }

    #[Test]
    public function UserManagementParentShowsPendingApprovalBadge(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('user-list', 'web'),
        ]);

        $response = $this->actingAs($admin)->get(route('affiliate.admin.index'));
        $menu = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<p>\s*User Management\s*<span[^>]*data-affiliate-pending-approval-badge[^>]*><\/span>\s*<\/p>\s*<i/s',
            $menu,
        );
        $this->assertMatchesRegularExpression(
            '/<p>\s*Users Affiliate\s*<span[^>]*data-affiliate-pending-approval-badge[^>]*><\/span>\s*<\/p>/s',
            $menu,
        );
    }

    #[Test]
    public function StudentsManagementParentShowsCentreBookShipmentBadge(): void
    {
        Department::query()->create([
            'name' => 'Navigation Hq',
            'code' => 'NAV-HQ',
            'status' => 1,
        ]);
        $department = Department::query()->create([
            'name' => 'Navigation Centre',
            'code' => 'NAV-CENTRE',
            'status' => 1,
        ]);
        $admin = User::factory()->create([
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('student-list', 'web'),
            Permission::findOrCreate('book-shipment-receive', 'web'),
        ]);

        $response = $this->actingAs($admin)->get(route('affiliate.admin.index'));
        $menu = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<p>\s*Students Management\s*<span[^>]*data-book-shipment-badge="centre"[^>]*><\/span>\s*<\/p>\s*<i/s',
            $menu,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<p>\s*Incoming Book Shipments\s*<span[^>]*data-book-shipment-badge="centre"/s',
            $menu,
        );
    }

    #[Test]
    public function PendingWorkNotificationIsRenderedInsideTheNavbar(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
        ]);

        $response = $this->actingAs($admin)->get(route('affiliate.admin.index'));
        $menu = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<nav[^>]*>.*data-pending-work-alert-toggle.*data-pending-work-alert-panel.*<\/nav>/s',
            $menu,
        );
        $this->assertStringNotContainsString('data-pending-work-alert-toast', $menu);
    }
}
