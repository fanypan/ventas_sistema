<?php

declare(strict_types=1);

namespace App\Providers;

use App\Jobs\SetupTenantJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events()
    {
        $queueProvisioning = config('queue.default') !== 'sync';

        return [
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                function (Events\TenantCreated $event) {
                    $tenant = $event->tenant;
                    if ($tenant->slug && $tenant->domains()->doesntExist()) {
                        $tenant->domains()->create([
                            'domain' => $tenant->slug.'.'.config('saas.tenant_base_domain'),
                        ]);
                    }
                },
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued($queueProvisioning),
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued($queueProvisioning),
            ],
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [
                function (Events\DatabaseMigrated $event) {
                    $key = 'tenant-setup-password:'.$event->tenant->getTenantKey();
                    $password = cache()->pull($key)
                        ?? $event->tenant->setup_password
                        ?? \Illuminate\Support\Str::random(12);
                    SetupTenantJob::dispatchSync($event->tenant, $password);
                },
            ],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],
            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],
            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [
                function () {
                    app()[PermissionRegistrar::class]->forgetCachedPermissions();
                },
            ],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
