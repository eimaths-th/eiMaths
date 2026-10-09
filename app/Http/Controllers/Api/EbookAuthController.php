<?php

namespace App\Http\Controllers\Api;

use App\Domain\Ebook\Audit;
use App\Domain\Ebook\SessionHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class EbookAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:60', 'password' => 'required|string|max:200',
            'device_id' => 'required|uuid', 'device_label' => 'required|string|max:100',
        ]);
        return DB::transaction(function () use ($data) {
            $students = DB::table('contacts')->where('code', $data['code'])->where('type', 'student')->lockForUpdate()->limit(2)->get();
            $student = $students->count() === 1 ? $students->first() : null;
            abort_unless($student && $student->password && Hash::check($data['password'], $student->password), 422, 'รหัสนักเรียนหรือรหัสผ่านไม่ถูกต้อง');
            abort_if((int) $student->discontinued === 1, 403, 'บัญชีนี้ไม่สามารถใช้งานได้');
            $existing = DB::table('ebook_sessions')->where('student_id', $student->id)->first();
            if ($existing && $existing->expires_at > now()->toDateTimeString() && $existing->absolute_expires_at > now()->toDateTimeString()) {
                Audit::record((object) ['student_id' => $student->id, 'id' => $existing->id, 'device_id' => $data['device_id']], 'login_blocked', metadata: ['reason' => 'active_session']);
                // Return (rather than throw) so the blocked-attempt audit is committed.
                return response()->json(['message' => 'บัญชีนี้มี Session ที่ใช้งานอยู่ กรุณาออกจากระบบเครื่องเดิม หรือรอ Session หมดอายุ', 'code' => 'session_active'], 409);
            }
            if ($existing) { SessionHistory::close($existing, 'expired'); }
            DB::table('ebook_sessions')->where('student_id', $student->id)->delete();
            $token = bin2hex(random_bytes(32));
            $session = (object) [
                'id' => (string) Str::uuid(), 'student_id' => $student->id,
                'device_id' => $data['device_id'], 'device_label' => $data['device_label'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(config('ebook.session_idle_minutes')),
                'absolute_expires_at' => now()->addHours(config('ebook.session_max_hours')),
                'created_at' => now(), 'updated_at' => now(),
            ];
            DB::table('ebook_sessions')->insert((array) $session);
            SessionHistory::remember($session);
            Audit::record($session, 'login');
            return response()->json([
                'token' => $token, 'session_id' => $session->id,
                'student' => ['id' => $student->id, 'code' => $student->code, 'name' => $student->nickname ?: $student->name],
            ])->header('Cache-Control', 'no-store');
        });
    }

    public function me(Request $request)
    {
        $student = $request->attributes->get('ebook_student');
        return response()->json(['session_id' => $request->attributes->get('ebook_session')->id,
            'student' => ['id' => $student->id, 'code' => $student->code, 'name' => $student->nickname ?: $student->name]]);
    }

    public function logout(Request $request)
    {
        $session = $request->attributes->get('ebook_session');
        Audit::record($session, 'logout');
        SessionHistory::close($session, 'logout');
        DB::table('ebook_sessions')->where('id', $session->id)->delete();
        return response()->json(['ok' => true]);
    }
}
