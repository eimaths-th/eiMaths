<?php

namespace App\Domain\Ebook;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class SessionHistory
{
    public static function remember(object $session): void
    {
        DB::table('ebook_session_history')->insertOrIgnore([
            'id' => $session->id, 'student_id' => $session->student_id,
            'device_id' => $session->device_id, 'device_label' => $session->device_label,
            'started_at' => Carbon::parse($session->created_at, config('app.timezone'))->utc(),
        ]);
    }

    public static function close(object $session, string $reason): void
    {
        self::remember($session);
        DB::table('ebook_session_history')->where('id', $session->id)->whereNull('ended_at')
            ->update(['ended_at' => now('UTC'), 'end_reason' => $reason]);
    }
}
