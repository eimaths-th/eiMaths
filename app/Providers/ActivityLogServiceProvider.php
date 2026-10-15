<?php

namespace App\Providers;

use App\Support\Activity\{ActivityObserver, AuthenticationActivity};
use Illuminate\Auth\Events\{Failed, Login, Logout};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->scoped(ActivityObserver::class);
        foreach (array_keys(config('activity-log.models')) as $class) {
            $class::observe(ActivityObserver::class);
        }
        foreach ([Login::class, Logout::class, Failed::class] as $event) {
            Event::listen($event, [AuthenticationActivity::class, 'handle']);
        }
    }
}
