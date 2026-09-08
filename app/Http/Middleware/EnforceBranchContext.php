<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnforceBranchContext
{
    public function handle(Request $request, Closure $next)
    {
        $actor = $request->user();
        if (!$actor instanceof User) {
            return $next($request);
        }
        $context = app(BranchContext::class);
        return $context->runAs($actor, function () use ($actor, $request, $next, $context) {
            if (!in_array($request->route()?->getActionMethod(), ['logout', 'perform', 'quizUserLogout'], true)) {
                abort_if((int) $actor->disable === 1, 403, 'This account is disabled.');
                abort_unless($actor->department_id, 403, 'This account needs a branch assignment.');
                if ($actor->department_id) {
                    abort_unless(DB::table('departments')->where('id', $actor->department_id)->where('status', 1)->exists(), 403, 'This branch is inactive.');
                }
            }
            app(\App\Support\Branch\BranchRouteAccess::class)->authorize($request, $actor);
            $response = str_ends_with(strtolower($request->route()?->getActionMethod() ?? ''), '_all')
                ? DB::transaction(fn () => $next($request))
                : $next($request);
            if ($response instanceof StreamedResponse) {
                $callback = $response->getCallback();
                $response->setCallback(fn () => $context->runAs($actor, $callback));
            }
            return $response;
        });
    }
}
