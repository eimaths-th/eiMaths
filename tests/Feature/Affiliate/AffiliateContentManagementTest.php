<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Services\AffiliateMediaStorageService;
use App\Domain\Affiliate\ValueObjects\StoredAffiliateMedia;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee('data-affiliate-content-media-type', false)
            ->assertSee('data-affiliate-content-media-file', false)
            ->assertSee('data-affiliate-content-media-help', false)
            ->assertSee('affiliate-content-media-type.js', false)
            ->assertSee('affiliate-publication-window.js', false)
            ->assertSee('data-affiliate-publication-unlimited-toggle', false)
            ->assertSee('id="affiliate-content-unlimited-' . $content->id . '"', false)
            ->assertSee('id="affiliate-create-unlimited"', false)
            ->assertSee('data-affiliate-publication-end-field="affiliate-content-end-' . $content->id . '"', false)
            ->assertSee('data-affiliate-publication-end-field="affiliate-create-end"', false)
            ->assertSee('data-affiliate-campaign-code-generate', false)
            ->assertSee('สุ่มรหัส')
            ->assertSee('เผยแพร่ไม่มีกำหนด')
            ->assertSee('<label for="affiliate-content-start-' . $content->id . '">เวลาเริ่มเผยแพร่</label>', false)
            ->assertSee('<label for="affiliate-content-end-' . $content->id . '">เวลาสิ้นสุดเผยแพร่</label>', false)
            ->assertSee('<label for="affiliate-create-start">เวลาเริ่มเผยแพร่</label>', false)
            ->assertSee('<label for="affiliate-create-end">เวลาสิ้นสุดเผยแพร่</label>', false)
            ->assertDontSee('$.ajax', false)
            ->assertDontSee('ลบสื่อนี้', false);
    }

    #[Test]
    public function CreateFormRetainsTheSelectedVideoTypeAfterValidationRedirect(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');

        $response = $this->withSession(['_old_input' => ['type' => 'video']])
            ->actingAs($admin)
            ->get(route('affiliate-content-manage.index'));

        $response->assertOk()
            ->assertSee('<option value="video" selected', false);
    }

    #[Test]
    public function CreateRequestTreatsCheckedUnlimitedPublicationAsHavingNoEndTime(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $this->mock(AffiliateMediaStorageService::class)
            ->shouldReceive('Store')
            ->once()
            ->andReturn(new StoredAffiliateMedia(
                type: 'video',
                relative_path: 'uploads/affiliate/video/unlimited.mp4',
                absolute_path: public_path('uploads/affiliate/video/unlimited.mp4'),
                mime_type: 'video/mp4',
                extension: 'mp4',
            ));

        $this->actingAs($admin)->post(route('affiliate-content-manage.store'), [
            'title' => 'Unlimited campaign',
            'type' => 'video',
            'file' => UploadedFile::fake()->create('unlimited.mp4', 10, 'video/mp4'),
            'description' => 'Always available campaign',
            'destination_target' => '/courses',
            'campaign_code' => 'unlimited-campaign',
            'publish_starts_at' => '2026-08-28T10:00',
            'publish_ends_at' => '2030-12-31T23:59',
            'publish_indefinitely' => '1',
        ])->assertRedirect();

        $content = AffiliateContent::query()->where('campaign_code', 'unlimited-campaign')->firstOrFail();
        $this->assertSame('2026-08-28 10:00:00', $content->publish_starts_at->format('Y-m-d H:i:s'));
        $this->assertNull($content->publish_ends_at);
    }

    #[Test]
    public function UpdateRequestTreatsCheckedUnlimitedPublicationAsHavingNoEndTime(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Scheduled campaign',
            'file_path' => 'uploads/affiliate/video/scheduled.mp4',
            'type' => 'video',
            'description' => 'Scheduled caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill([
            'campaign_code' => 'scheduled-campaign',
            'destination_target' => '/courses',
            'publish_starts_at' => '2026-08-01 00:00:00',
            'publish_ends_at' => '2026-08-31 23:59:00',
        ])->save();

        $this->actingAs($admin)->put(route('affiliate-content-manage.update', $content), [
            'title' => 'Always available campaign',
            'destination_target' => '/courses',
            'publish_starts_at' => '2026-08-28T10:00',
            'publish_indefinitely' => '1',
        ])->assertRedirect();

        $content->refresh();
        $this->assertSame('2026-08-28 10:00:00', $content->publish_starts_at->format('Y-m-d H:i:s'));
        $this->assertNull($content->publish_ends_at);
    }

    #[Test]
    public function PermittedAdminSeesUnlimitedPublicationStatusInTheContentList(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Always available content',
            'file_path' => 'uploads/affiliate/video/always-available.mp4',
            'type' => 'video',
            'description' => 'Always available caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill([
            'campaign_code' => 'always-available-campaign',
            'destination_target' => '/',
        ])->save();

        $this->actingAs($admin)
            ->get(route('affiliate-content-manage.index'))
            ->assertOk()
            ->assertSee('<span data-affiliate-publication-unlimited>ไม่มีกำหนด</span>', false);
    }

    #[Test]
    public function PermittedAdminCanGenerateAnUnusedCampaignCode(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $admin->assignRole('Admin');
        $content = AffiliateContent::create([
            'title' => 'Existing campaign',
            'file_path' => 'uploads/affiliate/image/existing.webp',
            'type' => 'image',
            'description' => 'Caption',
            'status' => 'enabled',
            'created_by' => $admin->id,
        ]);
        $content->forceFill([
            'campaign_code' => 'cmp-existing123',
            'destination_target' => '/',
        ])->save();

        $response = $this->actingAs($admin)->get('/affiliate-content-manage/generate-campaign-code');

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['campaign_code']);
        $campaign_code = $response->json('campaign_code');
        $this->assertMatchesRegularExpression('/\Acmp-[a-z0-9]{12}\z/', (string) $campaign_code);
        $this->assertNotSame('cmp-existing123', $campaign_code);
    }

    #[Test]
    public function OrdinaryAuthenticatedUsersCannotGenerateCampaignCodes(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        $this->actingAs($user)
            ->get('/affiliate-content-manage/generate-campaign-code')
            ->assertForbidden();
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
