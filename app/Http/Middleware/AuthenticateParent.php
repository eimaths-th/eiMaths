<?php

namespace App\Http\Middleware;

use App\Models\Parents;
use Closure;
use Illuminate\Http\Request;

class AuthenticateParent
{
    public function handle(Request $request, Closure $next)
    {
        $parent = $request->user();
        abort_unless($parent instanceof Parents, 401, 'Parent authentication required.');
        if ($request->filled('parent_id')) {
            abort_unless((int) $request->parent_id === (int) $parent->id, 403);
        }
        $request->merge(['parent_id' => $parent->id]);
        if ($request->filled('student_id')) {
            $ids = $parent->students()->pluck('id')->map(fn ($id) => (int) $id)->all();
            abort_unless(in_array((int) $request->student_id, $ids, true), 403, 'This student is not linked to your account.');
        }
        return $next($request);
    }
}
