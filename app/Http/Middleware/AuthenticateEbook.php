<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AuthenticateEbook
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        abort_unless($token && strlen($token) === 64, 401, 'กรุณาเข้าสู่ระบบอีกครั้ง');
        $session = DB::table('ebook_sessions')->where('token_hash', hash('sha256', $token))
            ->where('device_id', $request->header('X-Device-Id'))
            ->where('expires_at', '>', now())->where('absolute_expires_at', '>', now())->first();
        abort_unless($session, 401, 'Session หมดอายุ กรุณาเข้าสู่ระบบอีกครั้ง');
        $student = DB::table('contacts')->where('id', $session->student_id)
            ->where('type', 'student')->where(function ($q) { $q->whereNull('discontinued')->orWhere('discontinued', 0); })
            ->first(['id', 'code', 'name', 'nickname']);
        abort_unless($student, 403, 'บัญชีนี้ไม่สามารถใช้งานได้');
        $request->attributes->set('ebook_session', $session);
        $request->attributes->set('ebook_student', $student);
        DB::table('ebook_sessions')->where('id', $session->id)->update([
            'expires_at' => now()->addMinutes(config('ebook.session_idle_minutes')), 'updated_at' => now(),
        ]);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
