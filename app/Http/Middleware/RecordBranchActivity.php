<?php

namespace App\Http\Middleware;

use App\Support\Activity\ActivityRecorder;
use App\Support\Branch\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RecordBranchActivity
{
    public function handle(Request $request, Closure $next)
    {
        $actor = $request->user();
        $recorder = app(ActivityRecorder::class);
        if (!$actor instanceof \App\Models\User || !$recorder->module($request)) {
            return $next($request);
        }
        return app(BranchContext::class)->runAs($actor, function () use ($request, $next, $recorder) {
            $route = $request->route();
            $actions = config('activity-log.transactional_actions', [])[class_basename($route->getControllerClass())] ?? [];
            $wrapTransaction = in_array($route->getActionMethod(), $actions, true);
            $level = DB::transactionLevel();
            if ($wrapTransaction) {
                DB::beginTransaction();
            }
            try {
                $response = $next($request);
                $body = $response instanceof \Illuminate\Http\JsonResponse ? $response->getData(true) : [];
                $failed = $response->getStatusCode() >= 400
                    || !empty($body['errors']) || !empty($body['error'])
                    || (is_array($body) && array_key_exists('success', $body) && $body['success'] === false);
                if ($failed) {
                    if ($wrapTransaction) {
                        DB::rollBack($level);
                    }
                    $recorder->failure($request, $response->getStatusCode() >= 400 ? $response->getStatusCode() : 422);
                } elseif ($wrapTransaction) {
                    DB::commit();
                }
                return $response;
            } catch (\Throwable $exception) {
                if ($wrapTransaction) {
                    DB::rollBack($level);
                }
                $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode()
                    : match (true) {
                        $exception instanceof \Illuminate\Validation\ValidationException => 422,
                        $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                        $exception instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                        default => 500,
                    };
                $recorder->failure($request, $status);
                throw $exception;
            }
        });
    }
}
