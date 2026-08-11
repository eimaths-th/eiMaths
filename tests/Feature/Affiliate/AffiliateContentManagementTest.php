<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.administration', true);
        Permission::findOrCreate('affiliate-content-manage', 'web');
        Role::findOrCreate('Admin', 'web')->givePermissionTo('affiliate-content-manage');
    }

    public function test_content_management_requires_the_dedicated_permission_and_preserves_files_on_archive(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Managed content',
            'file_path' => 'uploads/affiliate/image/managed.webp',
            'type' => 'image',
            'description' => 'Caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill(['campaign_code' => 'managed-campaign', 'destination_target' => '/courses'])->save();

        $this->actingAs($admin)->get(route('affiliate-content-manage.index'))->assertOk();
        $this->actingAs($admin)->delete(route('affiliate-content-manage.destroy', $content))->assertRedirect();
        $this->assertDatabaseHas('affiliate_contents', ['id' => $content->id, 'file_path' => 'uploads/affiliate/image/managed.webp']);
        $this->assertNotNull(AffiliateContent::withTrashed()->find($content->id));
    }

    public function test_ordinary_authenticated_users_are_denied(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $this->actingAs($user)->get(route('affiliate-content-manage.index'))->assertForbidden();
    }

    #[Test]
    public function PermittedAdminSeesHistoricalCampaignMetadataAndStandardActionForms(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Managed content',
            'file_path' => 'uploads/affiliate/image/managed.webp',
            'type' => 'image',
            'description' => 'Caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill([
            'campaign_code' => 'managed-campaign',
            'destination_target' => '/courses',
            'publish_starts_at' => '2026-08-01 00:00:00',
            'publish_ends_at' => '2026-08-31 23:59:59',
        ])->save();

        $response = $this->actingAs($admin)->get(route('affiliate-content-manage.index'));

        $response->assertOk()
            ->assertSee('managed-campaign')
            ->assertSee('/courses')
            ->assertSee(route('affiliate-content-manage.update', $content), false)
            ->assertSee(route('affiliate-content-manage.toggle-status'), false)
            ->assertSee(route('affiliate-content-manage.destroy', $content), false)
            ->assertDontSee('href="#affiliate-create-media"', false)
            ->assertDontSee('<details', false)
            ->assertDontSee('affiliate-edit-panel', false)
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('class="modal fade"', false)
            ->assertDontSee('$.ajax', false)
            ->assertDontSee('ลบสื่อนี้', false);
    }

    #[Test]
    public function PaginatedContentManagementKeepsRowsOnSeparatePages(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');

        for ($content_number = 1; $content_number <= 11; $content_number++) {
            $content = AffiliateContent::create([
                'title' => 'Paginated content ' . $content_number,
                'file_path' => 'uploads/affiliate/image/page-' . $content_number . '.webp',
                'type' => 'image',
                'description' => 'Caption ' . $content_number,
                'status' => 'enabled',
                'created_by' => $admin->id,
            ]);
            $content->forceFill([
                'campaign_code' => 'paginated-content-' . $content_number,
                'destination_target' => '/',
            ])->save();
        }

        $this->actingAs($admin)->get(route('affiliate-content-manage.index'))
            ->assertOk()
            ->assertSee('Paginated content 11')
            ->assertDontSee('Paginated content 1</strong>', false)
            ->assertSee('content_page=2', false);

        $this->actingAs($admin)->get(route('affiliate-content-manage.index', ['content_page' => 2]))
            ->assertOk()
            ->assertSee('Paginated content 1')
            ->assertDontSee('Paginated content 11');
    }

    #[Test]
    public function PermittedAdminCanToggleStatusThroughTheProtectedForm(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Toggle content',
            'file_path' => 'uploads/affiliate/image/toggle.webp',
            'type' => 'image',
            'description' => 'Caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill(['campaign_code' => 'toggle-campaign', 'destination_target' => '/'])->save();

        $this->actingAs($admin)->post(route('affiliate-content-manage.toggle-status'), ['id' => $content->id])->assertRedirect();

        $this->assertDatabaseHas('affiliate_contents', ['id' => $content->id, 'status' => 'disabled']);
    }

    #[Test]
    public function PermittedAdminUsesTheAdminLteContentShell(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('affiliate-content-manage.index'));

        $response->assertOk()
            ->assertSee('content-header', false)
            ->assertSee('content', false)
            ->assertSee('container-fluid', false)
            ->assertSee('card card-primary', false);
    }
}
