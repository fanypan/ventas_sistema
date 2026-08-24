<?php

use App\Http\Controllers\HealthController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::get(config('observability.health_path', 'up'), HealthController::class)
                ->middleware('throttle:60,1')
                ->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\AuthenticateSession::class,
        ]);

        $middleware->web(replace: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class => \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class => \App\Http\Middleware\PreventRequestForgery::class,
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'tenant.subscription' => \App\Http\Middleware\EnsureTenantSubscription::class,
            'central' => \App\Http\Middleware\EnsureCentralDomain::class,
            'platform.access' => \App\Http\Middleware\EnsurePlatformAccess::class,
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $platformPath = config('saas.platform_path');

            if ($request->is($platformPath.'*') || $request->routeIs('platform.*')) {
                return route('platform.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            TenantCouldNotBeIdentifiedException::class,
        ]);

        $exceptions->render(function (TenantCouldNotBeIdentifiedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not Found.'], 404);
            }

            return response()->view('errors.404', ['exception' => $e], 404);
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('subscriptions:tick')->dailyAt('07:00');
        $schedule->command('tenants:backup')->dailyAt('02:30');
        $schedule->command('horizon:snapshot')->everyFiveMinutes()->when(
            fn () => (bool) config('observability.horizon_enabled')
        );
    })
    ->create();
