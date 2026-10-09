<?php

namespace App\Domain\Ebook;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class EventIngestor
{
    public const ACTIONS = ['pen', 'highlight', 'erase', 'text', 'rect', 'ellipse', 'crop', 'paste', 'move', 'resize', 'rotate', 'duplicate', 'delete', 'layer', 'undo', 'redo', 'page_leave', 'book_close', 'save_failed', 'capture_detected', 'capture_stopped', 'screenshot_detected', 'reader_hidden', 'reader_resumed', 'app_background', 'app_foreground'];

    public function __construct(private BookAccess $access) {}

    /** Permanent failures are acknowledged individually so one entry cannot block the queue. */
    public function ingest(object $session, array $events): array
    {
        $accepted = []; $rejected = [];
        foreach (array_values($events) as $index => $raw) {
            $validator = Validator::make(['event' => $raw], [
                'event' => 'required|array:event_id,action,book_id,page_id,metadata,client_at,session_id',
                'event.event_id' => 'required|uuid',
                'event.action' => ['required', Rule::in(self::ACTIONS)],
                'event.book_id' => 'nullable|integer|min:1', 'event.page_id' => 'nullable|integer|min:1',
                'event.session_id' => 'nullable|uuid', 'event.client_at' => 'required|date',
                'event.metadata' => 'present|array:object_id,source_page_id,source_rect,number,reason,tool,platform',
                'event.metadata.object_id' => 'sometimes|uuid',
                'event.metadata.source_page_id' => 'sometimes|integer|min:1',
                'event.metadata.source_rect' => 'sometimes|array|size:4',
                'event.metadata.source_rect.*' => 'numeric|between:0,1',
                'event.metadata.number' => 'sometimes|integer|min:1',
                'event.metadata.reason' => 'sometimes|string|max:120',
                'event.metadata.tool' => 'sometimes|string|max:30',
                'event.metadata.platform' => 'sometimes|string|max:30',
            ]);
            if ($validator->fails()) {
                $rejected[] = ['index' => $index, 'event_id' => is_array($raw) ? ($raw['event_id'] ?? null) : null,
                    'code' => 'invalid_event', 'message' => $validator->errors()->first()];
                continue;
            }
            $event = $validator->validated()['event'];
            // A retry of an already accepted UUID is acknowledged without rewriting its original context.
            if (DB::table('ebook_events')->where('student_id', $session->student_id)->where('event_id', $event['event_id'])->exists()) {
                $accepted[] = $event['event_id']; continue;
            }
            try {
                $origin = $event['session_id'] ?? null;
                if ($origin) {
                    abort_unless(DB::table('ebook_session_history')->where('id', $origin)
                        ->where('student_id', $session->student_id)->where('device_id', $session->device_id)->exists(), 422, 'Session does not belong to this student and device.');
                }
                $bookId = isset($event['book_id']) ? (int) $event['book_id'] : null;
                $pageId = isset($event['page_id']) ? (int) $event['page_id'] : null;
                $page = null;
                if ($bookId) { $this->access->book($session->student_id, $bookId); }
                if ($pageId) {
                    $page = $this->access->page($session->student_id, $pageId);
                    abort_if($bookId && $bookId !== (int) $page->book_id, 422, 'Page does not belong to this book.');
                    $bookId = (int) $page->book_id;
                }
                if (isset($event['metadata']['source_page_id'])) {
                    $source = $this->access->page($session->student_id, $event['metadata']['source_page_id']);
                    abort_unless((int) $source->book_id === $bookId && (!$page || $source->version_id === $page->version_id), 422, 'Crop source must belong to this book and edition.');
                }
                $occurredAt = Carbon::parse($event['client_at'])->utc();
                DB::table('ebook_events')->insertOrIgnore([
                    'event_id' => $event['event_id'], 'student_id' => $session->student_id,
                    'session_id' => $origin, 'received_session_id' => $session->id,
                    'session_origin' => $origin ? 'client' : 'unknown', 'device_id' => $session->device_id,
                    'book_id' => $bookId, 'page_id' => $pageId, 'action' => $event['action'], 'source' => 'client',
                    'metadata' => json_encode($event['metadata']), 'client_at' => $occurredAt,
                    'occurred_at' => $occurredAt, 'received_at' => now(),
                ]);
                $accepted[] = $event['event_id'];
            } catch (HttpExceptionInterface $e) {
                $rejected[] = ['index' => $index, 'event_id' => $event['event_id'],
                    'code' => $e->getStatusCode() === 404 ? 'content_unavailable' : 'invalid_context',
                    'message' => $e->getMessage() ?: 'The referenced content is not available to this student.'];
            }
        }
        return compact('accepted', 'rejected');
    }
}
