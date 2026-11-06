<?php

namespace App\Http\Middleware;

use App\Domain\Elearning\StudentSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentStudentSession
{
    public function __construct(private readonly StudentSessionService $student_sessions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();
        if (!$student || $this->student_sessions->IsCurrent($student, $request)) {
            return $next($request);
        }

        Auth::guard('student')->logout();
        $request->session()->forget(StudentSessionService::LOCAL_SESSION_KEYS);
        $message = __('students.login.session_replaced');
        $login_response = redirect()->route('student.login')->withErrors(['error' => $message]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'code' => 'ELEARNING_SESSION_REPLACED',
                'message' => $message,
                'login_url' => route('student.login'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $login_response;
    }
}
