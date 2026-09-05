<?php

namespace App\Providers;

use App\Models\PlatformUser;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::auth(function ($request) {
            $user = $request->user('platform');

            return $user !== null && $user->isAdmin();
        });
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return $user instanceof PlatformUser && $user->isAdmin();
        });
    }
}
