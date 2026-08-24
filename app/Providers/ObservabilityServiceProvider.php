<?php

namespace App\Providers;

use App\Observability\SentryScrubber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Sentry\Event as SentryEvent;
use Sentry\EventHint;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Stancl\Tenancy\Events\RevertedToCentralContext;
use Stancl\Tenancy\Events\TenancyInitialized;

class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (config('observability.horizon_enabled') && class_exists(\Laravel\Horizon\HorizonServiceProvider::class)) {
            $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
            $this->app->register(HorizonServiceProvider::class);
        }

        if (config('observability.telescope_enabled') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $this->configureSentry();
    }

    private function configureSentry(): void
    {
        if (! filled(config('sentry.dsn')) || ! class_exists(SentrySdk::class)) {
            return;
        }

        $client = SentrySdk::getCurrentHub()->getClient();
        if ($client) {
            $scrubber = $this->app->make(SentryScrubber::class);
            $client->getOptions()->setBeforeSendCallback(
                fn (SentryEvent $event, ?EventHint $hint) => $scrubber($event, $hint)
            );
        }

        Event::listen(TenancyInitialized::class, function () {
            \Sentry\configureScope(function (Scope $scope) {
                $scope->setTag('surface', 'tenant');
                $scope->setTag('tenant', (string) tenant('id'));
                $scope->setTag('tenant_slug', (string) tenant('slug'));
            });
        });

        Event::listen(RevertedToCentralContext::class, function () {
            \Sentry\configureScope(function (Scope $scope) {
                $scope->setTag('surface', 'central');
                $scope->removeTag('tenant');
                $scope->removeTag('tenant_slug');
            });
        });
    }
}
