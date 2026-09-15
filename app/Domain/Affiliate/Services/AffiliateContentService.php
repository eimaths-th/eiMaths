<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\ValueObjects\StoredAffiliateMedia;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use Illuminate\Support\Str;
use RuntimeException;

class AffiliateContentService
{
    private const CAMPAIGN_CODE_PREFIX = 'cmp-';
    private const CAMPAIGN_CODE_RANDOM_LENGTH = 12;
    private const CAMPAIGN_CODE_GENERATION_ATTEMPTS = 10;

    public function __construct(
        private readonly AffiliateDestinationValidator $destination_validator,
        private readonly AffiliateMediaStorageService $media_storage_service,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function GenerateUniqueCampaignCode(): string
    {
        for ($attempt = 0; $attempt < self::CAMPAIGN_CODE_GENERATION_ATTEMPTS; $attempt++) {
            $campaign_code = self::CAMPAIGN_CODE_PREFIX . Str::lower(Str::random(self::CAMPAIGN_CODE_RANDOM_LENGTH));
            $already_exists = DB::table('affiliate_contents')
                ->where('campaign_code', $campaign_code)
                ->exists();

            if (! $already_exists) {
                return $campaign_code;
            }
        }

        throw new RuntimeException('Unable to generate a unique Affiliate campaign code.');
    }

    public function Create(array $validated, UploadedFile $file, User $actor, string $correlation_id): AffiliateContent
    {
        $destination_target = $this->destination_validator->Normalize((string) ($validated['destination_target'] ?? '/'));
        $stored_media = $this->media_storage_service->Store($file, (string) $validated['type']);

        try {
            return DB::transaction(function () use ($validated, $actor, $correlation_id, $destination_target, $stored_media): AffiliateContent {
                $content = new AffiliateContent();
                $content->forceFill([
                    'title' => $validated['title'],
                    'file_path' => $stored_media->relative_path,
                    'type' => $validated['type'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'] ?? 'enabled',
                    'created_by' => $actor->id,
                    'destination_target' => $destination_target,
                    'campaign_code' => $validated['campaign_code'],
                    'publish_starts_at' => $validated['publish_starts_at'] ?? null,
                    'publish_ends_at' => $validated['publish_ends_at'] ?? null,
                ])->save();

                $this->audit_service->Record(
                    'content.created',
                    'affiliate_content',
                    $content->id,
                    $actor,
                    [],
                    ['campaign_code' => $content->campaign_code, 'status' => $content->status],
                    null,
                    'SUCCESS',
                    null,
                    $correlation_id,
                );

                return $content->fresh();
            });
        } catch (\Throwable $exception) {
            $this->media_storage_service->Delete($stored_media);
            throw $exception;
        }
    }

    public function Update(
        AffiliateContent $content,
        array $validated,
        User $actor,
        string $correlation_id,
        ?UploadedFile $replacement_file = null,
    ): AffiliateContent
    {
        if (array_key_exists('campaign_code', $validated) || array_key_exists('file', $validated)) {
            throw new LogicException('Affiliate campaign codes are immutable and replacement files must use the dedicated argument.');
        }

        $stored_replacement = $replacement_file === null
            ? null
            : $this->media_storage_service->Store($replacement_file, (string) $content->type);

        try {
            $result = DB::transaction(function () use ($content, $validated, $actor, $correlation_id, $stored_replacement): array {
                $locked = $this->LockWritableContent($content);
                if ($stored_replacement !== null && $stored_replacement->type !== $locked->type) {
                    throw new LogicException('The replacement file must match the existing Affiliate media type.');
                }

                $prior_state = [
                    'campaign_code' => $locked->campaign_code,
                    'status' => $locked->status,
                    'media_replaced' => false,
                ];
                $previous_file_path = (string) $locked->file_path;
                $previous_type = (string) $locked->type;
                $attributes = [];
                foreach (['title', 'description', 'status', 'publish_starts_at', 'publish_ends_at'] as $field) {
                    if (array_key_exists($field, $validated)) {
                        $attributes[$field] = $validated[$field];
                    }
                }
                if (array_key_exists('destination_target', $validated)) {
                    $attributes['destination_target'] = $this->destination_validator->Normalize((string) $validated['destination_target']);
                }
                if ($stored_replacement !== null) {
                    $attributes['file_path'] = $stored_replacement->relative_path;
                }
                $attributes['updated_by'] = $actor->id;
                $locked->forceFill($attributes)->save();
                $this->audit_service->Record(
                    'content.updated',
                    'affiliate_content',
                    $locked->id,
                    $actor,
                    $prior_state,
                    [
                        'campaign_code' => $locked->campaign_code,
                        'status' => $locked->status,
                        'media_replaced' => $stored_replacement !== null,
                    ],
                    null,
                    'SUCCESS',
                    null,
                    $correlation_id,
                );

                return [
                    'content' => $locked->fresh(),
                    'previous_file_path' => $previous_file_path,
                    'previous_type' => $previous_type,
                ];
            });
        } catch (\Throwable $exception) {
            if ($stored_replacement !== null) {
                $this->media_storage_service->Delete($stored_replacement);
            }
            throw $exception;
        }

        if ($stored_replacement !== null
            && $result['previous_file_path'] !== $stored_replacement->relative_path
            && ! AffiliateContent::withTrashed()->where('file_path', $result['previous_file_path'])->exists()) {
            $this->media_storage_service->DeleteStoredPath(
                $result['previous_file_path'],
                $result['previous_type'],
            );
        }

        return $result['content'];
    }

    public function ToggleStatus(AffiliateContent $content, User $actor, string $correlation_id): AffiliateContent
    {
        return DB::transaction(function () use ($content, $actor, $correlation_id): AffiliateContent {
            $locked = $this->LockWritableContent($content);
            $prior_state = ['campaign_code' => $locked->campaign_code, 'status' => $locked->status];
            $locked->forceFill(['status' => $locked->status === 'enabled' ? 'disabled' : 'enabled', 'updated_by' => $actor->id])->save();
            $this->audit_service->Record('content.updated', 'affiliate_content', $locked->id, $actor, $prior_state, ['campaign_code' => $locked->campaign_code, 'status' => $locked->status], null, 'SUCCESS', null, $correlation_id);

            return $locked->fresh();
        });
    }

    public function Archive(AffiliateContent $content, User $actor, string $correlation_id): void
    {
        DB::transaction(function () use ($content, $actor, $correlation_id): void {
            $locked = $this->LockWritableContent($content);
            $locked->forceFill(['archived_by' => $actor->id, 'archived_at' => now()])->save();
            $locked->delete();
            $this->audit_service->Record('content.archived', 'affiliate_content', $locked->id, $actor, ['campaign_code' => $locked->campaign_code, 'status' => $locked->status], ['campaign_code' => $locked->campaign_code, 'status' => 'archived'], null, 'SUCCESS', null, $correlation_id);
        });
    }

    private function LockWritableContent(AffiliateContent $content): AffiliateContent
    {
        $locked = AffiliateContent::withTrashed()->whereKey($content->id)->lockForUpdate()->firstOrFail();
        if ($locked->trashed() || $locked->archived_at !== null) {
            throw new LogicException('Archived Affiliate content cannot be changed.');
        }

        return $locked;
    }
}
