<?php

namespace App\Domain\Elearning;

use App\Models\{Test, User};
use Illuminate\Support\Facades\Auth;

final class QuizResultAccess
{
    public function AuthorizeStaff(string $permission_name): User
    {
        $actor = Auth::guard('web')->user();
        abort_unless($actor instanceof User && !Auth::guard('student')->check(), 403);
        abort_unless($actor->can($permission_name), 403);
        return $actor;
    }

    public function AuthorizePersonalOrStaff(Test $test): bool
    {
        if (Auth::guard('student')->check()) {
            abort_unless($test->type_user === 'student' && (int) $test->user_id === (int) Auth::guard('student')->id(), 403);
            return false;
        }
        $actor = Auth::guard('web')->user();
        abort_unless($actor instanceof User, 403);
        if ($test->type_user === 'user' && (int) $test->user_id === (int) $actor->id) {
            abort_unless($actor->can('quiz-list'), 403);
            return false;
        }
        $this->AuthorizeStaff('quiz-results-view');
        return true;
    }
}
