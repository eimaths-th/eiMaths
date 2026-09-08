<?php

namespace Tests\Feature\Navigation;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hq = Department::query()->create([
            'id' => Department::HQ_ID,
            'name' => 'HQ Navigation',
            'code' => 'HQ-NAV',
            'status' => 1,
        ]);
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function BranchTransferLivesInsideCentreManagement(): void
    {
        $menu = $this->RenderOwnerMenu(['branch-transfer', 'centre-manage']);
        $centre_position = strpos($menu, 'Centre Management');
        $transfer_position = strpos($menu, route('branch-transfers.index'));

        $this->assertNotFalse($centre_position);
        $this->assertNotFalse($transfer_position);
        $this->assertGreaterThan($centre_position, $transfer_position);
        $this->assertSame(1, substr_count($menu, route('branch-transfers.index')));
        $this->assertStringContainsString('nav-icon fas fa-building', $menu);
        $this->assertStringContainsString('nav-icon fas fa-exchange-alt', $menu);
    }

    #[Test]
    public function OperationalPagesAreGroupedByTheirWorkArea(): void
    {
        $menu = $this->RenderOwnerMenu([
            'student-list', 'course-manage', 'receipt-list', 'financial-settings-manage',
            'product-list', 'book-shipment-list', 'quiz-manage', 'blog-list', 'faq-list',
            'central-settings-manage', 'all-centre',
        ]);

        foreach ([
            'Students Management' => 'Students List',
            'Course Management' => 'Course Applications',
            'Finance &amp; Documents' => 'Payment Receipts',
            'Stock &amp; Logistics' => 'Products',
            'Quiz Management' => 'Question Bank',
            'Content Management' => 'Blog',
        ] as $group => $child) {
            $group_position = strpos($menu, $group);
            $child_position = strpos($menu, $child);
            $this->assertNotFalse($group_position, "Missing menu group {$group}");
            $this->assertNotFalse($child_position, "Missing child menu {$child}");
            $this->assertGreaterThan($group_position, $child_position, "{$child} must follow {$group}");
        }
    }

    #[Test]
    public function AdministrationPagesUseDedicatedGroupsAndIcons(): void
    {
        $menu = $this->RenderOwnerMenu([
            'user-list', 'role-list', 'affiliate-list', 'affiliate-config-edit',
            'centre-manage', 'department-list', 'branch-transfer',
            'integration-settings-manage', 'central-settings-manage', 'all-centre',
        ]);

        foreach (['Affiliate Management', 'User &amp; Access', 'Centre Management', 'System Settings'] as $group) {
            $this->assertStringContainsString($group, $menu);
        }
        foreach ([
            'fas fa-handshake', 'fas fa-users-cog', 'fas fa-building', 'fas fa-cogs',
            'fas fa-user-tag', 'fas fa-key', 'fas fa-plug',
        ] as $icon) {
            $this->assertStringContainsString($icon, $menu);
        }
    }

    private function RenderOwnerMenu(array $permissions): string
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $owner = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $owner->assignRole(Role::findOrCreate('SystemAdmin', 'web'));
        $this->actingAs($owner);

        return view('layouts.menu', ['book_shipment_alert_role' => null])->render();
    }
}
