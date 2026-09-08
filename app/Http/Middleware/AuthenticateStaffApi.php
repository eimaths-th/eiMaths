<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Branch\BranchAccess;
use Closure;
use Illuminate\Http\Request;

class AuthenticateStaffApi
{
    public function handle(Request $request, Closure $next)
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401, 'Staff authentication required.');
        abort_if((int) $actor->disable === 1, 403);
        $method = $request->route()->getActionMethod();
        $permission = in_array($method, ['saveHistories', 'saveFile'], true) ? 'history-create' : 'student-list';
        abort_unless($actor->can($permission), 403);
        if ($request->filled('centre')) {
            BranchAccess::assertBranch($actor, $request->centre, !$request->isMethodSafe());
        }
        foreach (['student', 'student_id', 'std_id'] as $field) {
            if ($request->filled($field)) {
                $student = \App\Models\Contact::query()->find($request->input($field));
                abort_unless($student, 403, 'Student access denied.');
                if ($request->filled('centre')) { abort_unless((int) $student->centre === (int) $request->centre, 422); }
            }
        }
        $request->merge(['userid' => $actor->id, 'user_id' => $actor->id]);
        return $next($request);
    }
}
