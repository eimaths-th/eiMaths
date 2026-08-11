<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Services\AffiliateContentService;
use App\Domain\Affiliate\Services\AffiliateMediaStorageService;
use App\Domain\Affiliate\ValueObjects\StoredAffiliateMedia;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateContentServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CreatesUpdatesTogglesAndArchivesContentWithSanitizedAuditSignals(): void
    {
        $actor = User::factory()->create(['password' => Hash::make('content-service-password')]);
        $stored_media = new StoredAffiliateMedia(
            type: 'image',
            relative_path: 'uploads/affiliate/image/content.webp',
            absolute_path: public_path('uploads/affiliate/image/content.webp'),
            mime_type: 'image/webp',
            extension: 'webp',
        );
        $storage = $this->mock(AffiliateMediaStorageService::class);
        $storage->shouldReceive('Store')->once()->andReturn($stored_media);

        $service = app(AffiliateContentService::class);
        $content = $service->Create([
            'title' => 'Campaign title',
            'description' => 'Private caption',
            'type' => 'image',
            'status' => 'enabled',
            'destination_target' => '/courses/math',
            'campaign_code' => 'service-campaign',
        ], UploadedFile::fake()->image('content.png'), $actor, 'correlation-service');

        $this->assertSame('/courses/math', $content->destination_target);
        $this->assertSame('service-campaign', $content->campaign_code);

        $updated = $service->Update($content, [
            'title' => 'Updated title',
            'description' => 'Updated caption',
            'destination_target' => '/courses/algebra',
            'status' => 'disabled',
        ], $actor, 'correlation-service');
        $this->assertSame('/courses/algebra', $updated->destination_target);
        $this->assertSame('disabled', $updated->status);

        $toggled = $service->ToggleStatus($updated, $actor, 'correlation-service');
        $this->assertSame('enabled', $toggled->status);

        $service->Archive($toggled, $actor, 'correlation-service');
        $archived = AffiliateContent::withTrashed()->findOrFail($content->id);
        $this->assertNotNull($archived->archived_at);
        $this->assertSame($actor->id, $archived->archived_by);
        $this->assertSame($stored_media->relative_path, $archived->file_path);
        $this->assertNull(AffiliateContent::find($content->id));

        $this->assertSame(4, AffiliateAuditLog::query()->where('correlation_id', 'correlation-service')->count());
        foreach (AffiliateAuditLog::query()->where('correlation_id', 'correlation-service')->get() as $audit) {
            $resulting_state = $audit->resulting_state ?? [];
            $this->assertArrayNotHasKey('destination_target', $resulting_state);
            $this->assertArrayNotHasKey('title', $resulting_state);
            $this->assertArrayNotHasKey('file_path', $resulting_state);
        }
    }

    #[Test]
    public function CampaignMutationAndArchivedWritesAreRejected(): void
    {
        $actor = User::factory()->create(['password' => Hash::make('content-service-password')]);
        $content = AffiliateContent::create([
            'title' => 'Existing content',
            'file_path' => 'uploads/affiliate/existing.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $content->forceFill([
            'destination_target' => '/',
            'campaign_code' => 'existing-campaign',
        ])->save();
        $service = app(AffiliateContentService::class);

        $this->expectException(LogicException::class);
        $service->Update($content, ['campaign_code' => 'changed-campaign'], $actor, 'correlation-reject');
    }
}
