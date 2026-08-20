<?php

namespace Modules\Purchases\Providers;

use App\Support\TenantMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Purchases\Http\Controllers';

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
            ->group(module_path('Purchases', '/Routes/web.php'));
    }
}
