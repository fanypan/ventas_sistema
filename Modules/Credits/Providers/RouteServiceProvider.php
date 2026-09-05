<?php

namespace Modules\Credits\Providers;

use App\Support\TenantMiddleware;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Credits\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware(TenantMiddleware::web())
            ->namespace($this->moduleNamespace)
            ->group(module_path('Credits', '/Routes/web.php'));
    }
}
