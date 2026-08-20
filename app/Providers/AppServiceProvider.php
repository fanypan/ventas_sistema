<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Services\Sifen\SifenGateway::class, function () {
            return config('saas.sifen_driver') === 'partner'
                ? new \App\Services\Sifen\PartnerSifenGateway()
                : new \App\Services\Sifen\NullSifenGateway();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
