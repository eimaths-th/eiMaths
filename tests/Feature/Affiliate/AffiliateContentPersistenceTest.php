<?php

namespace Tests\Feature\Affiliate;

use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateContentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function campaign_backfill_is_deterministic_and_idempotent(): void
    {
        $user = User::factory()->create(['password' => Hash::make('content-password')]);
        $content = AffiliateContent::create([
            'title' => 'Legacy media',
            'file_path' => 'uploads/affiliate/legacy.png',
            'type' => 'image',
            'status' => 'disabled',
            'created_by' => $user->id,
        ]);
        DB::table('affiliate_contents')->where('id', $content->id)->update([
            'destination_target' => null,
            'campaign_code' => null,
        ]);

        $migration = require base_path('database/migrations/2026_08_11_000003_extend_affiliate_contents_for_campaigns.php');
        $migration->up();
        $first = DB::table('affiliate_contents')->where('id', $content->id)->first();
        $migration->up();
        $second = DB::table('affiliate_contents')->where('id', $content->id)->first();

        $this->assertSame('/', $first->destination_target);
        $this->assertSame('legacy-' . $content->id, $first->campaign_code);
        $this->assertSame($first->destination_target, $second->destination_target);
        $this->assertSame($first->campaign_code, $second->campaign_code);
        $this->assertSame('disabled', $second->status);
    }

    #[Test]
    public function campaign_codes_are_unique_across_active_and_soft_deleted_rows(): void
    {
        $first = AffiliateContent::create([
            'title' => 'Active campaign',
            'file_path' => 'uploads/affiliate/active.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $first->forceFill(['destination_target' => '/', 'campaign_code' => 'unique-campaign'])->save();
        $first->delete();

        $this->expectException(QueryException::class);
        $duplicate = AffiliateContent::create([
            'title' => 'Duplicate campaign',
            'file_path' => 'uploads/affiliate/duplicate.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $duplicate->forceFill(['destination_target' => '/', 'campaign_code' => 'unique-campaign'])->save();
    }

    #[Test]
    public function publication_archive_casts_and_actor_relations_are_available(): void
    {
        $user = User::factory()->create(['password' => Hash::make('content-password')]);
        $content = AffiliateContent::create([
            'title' => 'Published campaign',
            'file_path' => 'uploads/affiliate/published.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $content->forceFill([
            'destination_target' => '/',
            'campaign_code' => 'published-campaign',
            'publish_starts_at' => '2026-08-01 00:00:00',
            'publish_ends_at' => '2026-08-31 23:59:59',
            'updated_by' => $user->id,
            'archived_by' => $user->id,
            'archived_at' => '2026-08-10 00:00:00',
        ])->save();
        $content = $content->fresh();

        $this->assertSame('2026-08-01', $content->publish_starts_at->format('Y-m-d'));
        $this->assertSame('2026-08-31', $content->publish_ends_at->format('Y-m-d'));
        $this->assertSame($user->id, $content->Updater->id);
        $this->assertSame($user->id, $content->Archiver->id);
        $content->delete();
        $this->assertNull(AffiliateContent::find($content->id));
        $this->assertNotNull(AffiliateContent::withTrashed()->find($content->id));
    }
}
