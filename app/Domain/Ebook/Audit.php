<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class Audit
{
    public static function record(object $session, string $action, ?int $bookId = null, ?int $pageId = null, array $metadata = []): void
    {
        DB::table('ebook_events')->insert([
            'event_id' => (string) Str::uuid(), 'student_id' => $session->student_id,
            'session_id' => $session->id ?? null, 'device_id' => $session->device_id ?? null,
            'book_id' => $bookId, 'page_id' => $pageId, 'action' => $action,
            'source' => 'server', 'metadata' => json_encode($metadata), 'received_at' => now(),
            'occurred_at' => now('UTC'), 'received_session_id' => $session->id ?? null,
            'session_origin' => 'server',
        ]);
    }
}
