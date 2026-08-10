<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('affiliate-registration', function (Request $request) {
            $max_attempts = max(1, (int) config('affiliate.abuse.registration_attempts_per_minute', 5));

            return Limit::perMinute($max_attempts)
                ->by($request->ip())
                ->response(function (Request $limited_request, array $headers) {
                    Log::warning('affiliate.registration.rate_limited', [
                        'ip' => $limited_request->ip(),
                        'route' => $limited_request->route()?->getName(),
                    ]);

                    return response(
                        'Too many Affiliate registration attempts. Please try again later.',
                        429,
                        $headers,
                    );
                });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            /* Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web2.php')); */
            Route::group([
                'middleware' => 'web',
                'namespace' => $this->namespace,
            ], function ($router) {
                $routes = glob(base_path('routes/*.php'));
                foreach ($routes as $route) {
                    require $route;
                }
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
