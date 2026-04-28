<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestQuizAuth
{
    /**
     * Handle an incoming request.
     * ตรวจสอบว่า Guest login อยู่ + ยังไม่หมดอายุ
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจว่า login ด้วย guest_quiz guard หรือไม่
        if (!Auth::guard('guest_quiz')->check()) {
            return redirect()->route('student.login')
                ->withErrors(['error' => 'กรุณาเข้าสู่ระบบก่อนทดสอบ']);
        }

        $guest = Auth::guard('guest_quiz')->user();

        // ตรวจว่าหมดอายุหรือยัง
        if ($guest->isExpired()) {
            // ลบข้อมูลการสอบ (เก็บข้อมูลส่วนตัวไว้)
            $guest->deleteTestData();

            // Logout
            Auth::guard('guest_quiz')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect ไปหน้าหมดอายุ
            return redirect()->route('guest.quiz.expired');
        }

        return $next($request);
    }
}
