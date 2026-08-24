<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::get(config('observability.health_path', 'up'), \App\Http\Controllers\HealthController::class)
                ->middleware('throttle:60,1')
                ->name('health');

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
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
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('platform-login', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('tenant-login', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            return Limit::perMinute(5)->by(
                mb_strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });
    }
}
