<?php

use App\Providers\AdminProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\ObservabilityServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    ObservabilityServiceProvider::class,
    EventServiceProvider::class,
    RouteServiceProvider::class,
    TenancyServiceProvider::class,
    AdminProvider::class,
];
