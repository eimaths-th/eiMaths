<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
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
}
