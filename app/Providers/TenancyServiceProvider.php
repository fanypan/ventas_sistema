<?php

declare(strict_types=1);

namespace App\Providers;

use App\Jobs\SetupTenantJob;
use App\Jobs\Tenant\CreateTenantDatabaseJob;
use App\Jobs\Tenant\MigrateTenantDatabaseJob;
use App\Support\TenantDatabaseName;
use App\Support\TenantPermissionCache;
use App\Support\TenantSetupPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\DatabaseConfig;
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
            Events\CreatingTenant::class => [
                function (Events\CreatingTenant $event) {
                    TenantSetupPassword::captureFromCreating($event->tenant);
                },
            ],
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
                    CreateTenantDatabaseJob::class,
                    MigrateTenantDatabaseJob::class,
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
            Events\CreatingDatabase::class => [
                function (Events\CreatingDatabase $event) {
                    $tenant = $event->tenant;

                    if ($tenant->provisioned_at !== null) {
                        return;
                    }

                    $manager = $tenant->database()->manager();
                    $name = $tenant->database()->getName();

                    if ($manager->databaseExists($name)) {
                        $manager->deleteDatabase($tenant);
                    }
                },
            ],
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [
                function (Events\DatabaseMigrated $event) {
                    $password = TenantSetupPassword::pull((string) $event->tenant->getTenantKey())
                        ?? Str::random(12);

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
                    TenantPermissionCache::scopeToCurrentTenant();
                },
            ],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [
                function () {
                    TenantPermissionCache::scopeToCentral();
                },
            ],
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
        DatabaseConfig::generateDatabaseNamesUsing(
            fn ($tenant) => TenantDatabaseName::for($tenant)
        );

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
