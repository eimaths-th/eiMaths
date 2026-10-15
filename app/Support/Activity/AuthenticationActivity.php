<?php

namespace App\Support\Activity;

use App\Models\User;
use Illuminate\Auth\Events\{Failed, Login, Logout};
use Illuminate\Support\Facades\DB;

final class AuthenticationActivity
{
    public function handle(Login|Logout|Failed $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }
        $user = $event->user;
        if ($event instanceof Failed) {
            // Failed attempts cannot establish an actor or branch, even for a known email.
            if (request()->route()?->getActionName() !== 'App\\Http\\Controllers\\Auth\\LoginController@login') {
                return;
            }
            app(ActivityRecorder::class)->write([
                'category' => 'authentication', 'module' => 'authentication', 'action' => 'login.failed',
                'outcome' => 'failed', 'branch_id' => null, 'subject_type' => 'authentication',
            ], null, false);
            return;
        }
        if (!$user instanceof User || !$user->roles->contains(fn ($role) => !in_array($role->name, ['Affiliate-user', 'User'], true))) {
            return;
        }
        if ($event instanceof Logout && request()->attributes->get('activity_login_denied')) {
            return;
        }
        $reason = null;
        if ($event instanceof Login) {
            $reason = (int) $user->disable === 1 ? 'account_disabled'
                : (!$user->department_id ? 'branch_missing'
                    : (!DB::table('departments')->where('id', $user->department_id)->where('status', 1)->exists() ? 'branch_inactive' : null));
            if ($reason) { request()->attributes->set('activity_login_denied', true); }
        }
        app(ActivityRecorder::class)->write([
            'category' => 'authentication', 'module' => 'authentication',
            'action' => $event instanceof Logout ? 'logout' : ($reason ? 'login.denied' : 'login'),
            'outcome' => $reason ? 'denied' : 'success',
            'branch_id' => $user->department_id, 'subject_type' => User::class,
            'subject_id' => $user->id, 'subject_is_owner' => $user->hasRole('SystemAdmin', 'web'),
            'metadata' => $reason ? ['reason' => $reason] : [],
        ], $user, false);
    }
}
