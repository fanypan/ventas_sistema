<?php

use App\Exceptions\BusinessRuleException;
use App\Exceptions\RenderJsonEnvelope;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\EnsureAdminPasswordIsSet;
use App\Http\Middleware\EnsureCentralDomain;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsurePlatformAccess;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsurePlatformPermission;
use App\Http\Middleware\EnsureTenantSubscription;
use App\Http\Middleware\PreventRequestForgery;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Responses\JsonEnvelope;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
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
            TrustHosts::class,
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        $middleware->web(append: [
            AuthenticateSession::class,
        ]);

        $middleware->web(replace: [
            EncryptCookies::class => App\Http\Middleware\EncryptCookies::class,
            Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class => PreventRequestForgery::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'tenant.subscription' => EnsureTenantSubscription::class,
            'plan.feature' => EnsurePlanFeature::class,
            'tenant.password' => EnsureAdminPasswordIsSet::class,
            'central' => EnsureCentralDomain::class,
            'platform.access' => EnsurePlatformAccess::class,
            'platform.admin' => EnsurePlatformAdmin::class,
            'platform.permission' => EnsurePlatformPermission::class,
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
            BusinessRuleException::class,
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => JsonEnvelope::wantsJson($request)
        );

        $exceptions->respond(new RenderJsonEnvelope);

        $exceptions->render(function (TenantCouldNotBeIdentifiedException $e, Request $request) {
            if (JsonEnvelope::wantsJson($request)) {
                return JsonEnvelope::error(JsonEnvelope::messageForStatus(404), null, 404);
            }

            return response()->view('errors.404', ['exception' => $e], 404);
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->routeIs('password.setup.show', 'password.setup.store') || $request->is('activar')) {
                return response()->view('auth.setup-password-invalid', [], 403);
            }
        });

        $exceptions->render(function (BusinessRuleException $e, Request $request) {
            if (JsonEnvelope::wantsJson($request)) {
                return response()->json($e->payload(), $e->status());
            }

            return back()->with('error', $e->getMessage());
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
