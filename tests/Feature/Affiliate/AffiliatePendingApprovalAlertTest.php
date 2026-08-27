<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliatePendingApprovalAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function AuthorizedAffiliateListUserCanReadCurrentPendingApprovalSummary(): void
    {
        $pending_user = User::factory()->create([
            'name' => 'Pending Affiliate',
            'password' => Hash::make('secret'),
        ]);
        AffiliateProfile::create([
            'user_id' => $pending_user->id,
            'referral_code' => 'AFF-PENDING-'.$pending_user->id,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);

        $pending_email_user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $pending_email_user->id,
            'referral_code' => 'AFF-EMAIL-'.$pending_email_user->id,
            'status' => AffiliateProfileStatus::PENDING_EMAIL,
        ]);

        $active_user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $active_user->id,
            'referral_code' => 'AFF-ACTIVE-'.$active_user->id,
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        $admin = $this->CreateAdminWithPermission('affiliate-list');

        $this->actingAs($admin)
            ->getJson('/admin/affiliates/pending-approval-summary')
            ->assertOk()
            ->assertJsonPath('data.pending_count', 1)
            ->assertJsonPath('data.latest_pending_affiliate.id', $pending_user->AffiliateProfile->id)
            ->assertJsonPath('data.latest_pending_affiliate.name', 'Pending Affiliate')
            ->assertJsonPath('data.latest_event_status', AffiliateProfileStatus::ACTIVE->value);
    }

    #[Test]
    public function AuthorizedAffiliateListUserSeesPendingApprovalAlertHooksInTheBackendLayout(): void
    {
        $admin = $this->CreateAdminWithPermission('affiliate-list');

        $this->actingAs($admin)
            ->get(route('affiliate.admin.index'))
            ->assertOk()
            ->assertSee('data-affiliate-pending-approval-alert', false)
            ->assertSee('data-pending-work-alert', false)
            ->assertSee('data-affiliate-pending-approval-badge', false)
            ->assertSee(route('affiliate.admin.pending-summary'), false);
    }

    #[Test]
    public function UserWithoutAffiliateListPermissionCannotReadPendingApprovalSummary(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);

        $this->actingAs($user)
            ->getJson('/admin/affiliates/pending-approval-summary')
            ->assertForbidden();
    }

    #[Test]
    public function DisabledAffiliateListUserCannotReadPendingApprovalSummary(): void
    {
        $admin = $this->CreateAdminWithPermission('affiliate-list');
        $admin->forceFill(['disable' => 1])->save();

        $this->actingAs($admin->fresh())
            ->getJson('/admin/affiliates/pending-approval-summary')
            ->assertForbidden();
    }

    private function CreateAdminWithPermission(string $permission_name): User
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));

        return $admin->fresh();
    }
}
