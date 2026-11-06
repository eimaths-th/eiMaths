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

    private const LOGINS_PER_MINUTE_BY_IP = 30;

    private const LOGINS_PER_MINUTE_BY_ACCOUNT = 6;

    private const REGISTRATIONS_PER_MINUTE_BY_IP = 5;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('ebook-login', function (Request $request) {
            $code = is_string($request->input('code')) ? strtolower($request->input('code')) : '';
            return [
                Limit::perMinute(30)->by('ebook-login-ip:'.$request->ip()),
                Limit::perMinute(6)->by('ebook-login-account:'.$request->ip().':'.hash('sha256', $code)),
            ];
        });
        RateLimiter::for('student-login', fn (Request $request): array => $this->CreateLoginLimits($request, 'code', 'error'));
        RateLimiter::for('guest-login', fn (Request $request): array => $this->CreateLoginLimits($request, 'guest_email', 'guest_error'));
        RateLimiter::for('guest-register', function (Request $request) {
            return Limit::perMinute(self::REGISTRATIONS_PER_MINUTE_BY_IP)
                ->by('guest-register-ip:'.$request->ip())
                ->response($this->CreateFormLimitResponse('registration'));
        });
        RateLimiter::for('ebook-api', function (Request $request) {
            return Limit::perMinute(180)->by('ebook-api:'.($request->attributes->get('ebook_session')?->id ?? $request->ip()));
        });

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

        RateLimiter::for('affiliate-click', function (Request $request) {
            $max_attempts = max(1, (int) config('affiliate.abuse.click_attempts_per_minute', 120));
            $user_agent = strtolower(trim((string) $request->header('User-Agent', '')));
            $transient_key = hash('sha256', $request->ip() . "\n" . $user_agent);

            return Limit::perMinute($max_attempts)
                ->by('affiliate-click:' . $transient_key)
                ->response(function (Request $limited_request, array $headers) {
                    return response('Too many requests. Please try again later.', 429, $headers);
                });
        });

        RateLimiter::for('affiliate-employee-enrollment', function (Request $request) {
            $max_attempts = max(1, (int) config('affiliate.abuse.employee_enrollment_attempts_per_minute', 6));

            return Limit::perMinute($max_attempts)
                ->by('affiliate-employee-enrollment:' . (string) $request->user()?->getAuthIdentifier())
                ->response(function (Request $limited_request, array $headers) {
                    Log::warning('affiliate.employee_enrollment.rate_limited', [
                        'user_id' => $limited_request->user()?->getAuthIdentifier(),
                        'route' => $limited_request->route()?->getName(),
                        'correlation_id' => $limited_request->attributes->get('affiliate_correlation_id'),
                        'operation' => 'affiliate.employee-enrollment.store',
                    ]);

                    return response(__('affiliate.employee_access.rate_limited'), 429, $headers);
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
                $routes = array_map(fn ($name) => base_path('routes/'.$name.'.php'), [
                    'web2', 'affiliate', 'credit-notes', 'branch-access',
                ]);
                foreach ($routes as $route) {
                    require $route;
                }
            });
        });
    }

    private function CreateLoginLimits(Request $request, string $account_field, string $error_key): array
    {
        $account_identifier = is_string($request->input($account_field)) ? strtolower($request->input($account_field)) : '';
        $limit_response = $this->CreateFormLimitResponse($error_key);

        return [
            Limit::perMinute(self::LOGINS_PER_MINUTE_BY_IP)
                ->by($account_field.'-login-ip:'.$request->ip())
                ->response($limit_response),
            Limit::perMinute(self::LOGINS_PER_MINUTE_BY_ACCOUNT)
                ->by($account_field.'-login-account:'.hash('sha256', $account_identifier))
                ->response($limit_response),
        ];
    }

    private function CreateFormLimitResponse(string $error_key): callable
    {
        return fn (Request $limited_request) => back()
            ->withErrors([$error_key => __('students.login.rate_limited')])
            ->withInput($limited_request->except(['password', 'guest_password', 'recaptcha_token']));
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
