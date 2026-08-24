<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Used by laravel/ui auth controllers after tenant login.
     */
    public const HOME = '/home';
}
