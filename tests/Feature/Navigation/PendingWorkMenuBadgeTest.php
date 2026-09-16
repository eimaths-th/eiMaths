<?php

namespace Tests\Feature\Navigation;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PendingWorkMenuBadgeTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hq = Department::query()->create([
            'id' => Department::HQ_ID,
            'name' => 'Navigation HQ',
            'code' => 'NAV-HQ',
            'status' => 1,
        ]);
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function AffiliateManagementOwnsAffiliateAccountsCourseApplicationsAndPendingBadges(): void
    {
        $admin = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('affiliate-course-application-list', 'web'),
            Permission::findOrCreate('course-manage', 'web'),
        ]);

        $menu = $this->actingAs($admin)->get(route('affiliate.admin.index'))->getContent();

        $this->assertStringContainsString('Affiliate Management', $menu);
        $this->assertStringContainsString('Affiliate Accounts', $menu);
        $this->assertMatchesRegularExpression(
            '/<p>\s*Affiliate Management\s*<span[^>]*data-affiliate-management-pending-badge/s',
            $menu,
        );
        $this->assertMatchesRegularExpression(
            '/<p>\s*Affiliate Accounts\s*<span[^>]*data-affiliate-pending-approval-badge[^>]*><\/span>\s*<\/p>/s',
            $menu,
        );
        $this->assertMatchesRegularExpression(
            '/<p[^>]*title="Affiliate Course Applications"[^>]*>.*data-affiliate-course-application-badge/s',
            $menu,
        );
    }

    #[Test]
    public function AffiliateBadgeIsNoLongerAttachedToUserAccess(): void
    {
        $admin = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('user-list', 'web'),
        ]);

        $menu = $this->actingAs($admin)->get(route('affiliate.admin.index'))->getContent();

        $this->assertStringContainsString('User &amp; Access', $menu);
        $this->assertDoesNotMatchRegularExpression(
            '/<p>\s*User &amp; Access\s*<span[^>]*data-affiliate-pending-approval-badge/s',
            $menu,
        );
        $this->assertMatchesRegularExpression(
            '/<p>\s*Affiliate Management\s*<span[^>]*data-affiliate-management-pending-badge/s',
            $menu,
        );
    }

    #[Test]
    public function StockAndLogisticsParentShowsCentreBookShipmentBadge(): void
    {
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
            Permission::findOrCreate('student-list', 'web'),
            Permission::findOrCreate('book-shipment-receive', 'web'),
        ]);

        $this->actingAs($admin);
        $menu = view('layouts.menu', ['book_shipment_alert_role' => 'centre'])->render();

        $this->assertMatchesRegularExpression(
            '/<p>\s*Stock &amp; Logistics\s*<span[^>]*data-book-shipment-badge="centre"[^>]*><\/span>\s*<\/p>\s*<i/s',
            $menu,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<p>\s*Students Management\s*<span[^>]*data-book-shipment-badge/s',
            $menu,
        );
    }

    #[Test]
    public function PendingWorkNotificationIsRenderedInsideTheNavbar(): void
    {
        $admin = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo(Permission::findOrCreate('affiliate-list', 'web'));

        $menu = $this->actingAs($admin)->get(route('affiliate.admin.index'))->getContent();

        $this->assertMatchesRegularExpression(
            '/<nav[^>]*>.*data-pending-work-alert-toggle.*data-pending-work-alert-panel.*<\/nav>/s',
            $menu,
        );
        $this->assertStringNotContainsString('data-pending-work-alert-toast', $menu);
    }
}
