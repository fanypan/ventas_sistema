<?php

namespace Modules\Financials\Providers;

use App\Support\TenantMiddleware;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Financials\Http\Controllers';

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
            ->group(module_path('Financials', '/Routes/web.php'));
    }
}
