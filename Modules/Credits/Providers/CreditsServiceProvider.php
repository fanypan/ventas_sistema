<?php

namespace Modules\Credits\Providers;

use Illuminate\Support\ServiceProvider;

class CreditsServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Credits';

    protected $moduleNameLower = 'credits';

    public function boot()
    {
        $this->registerViews();
        // Tenant migrations run via stancl/tenancy.
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    public function registerViews()
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
