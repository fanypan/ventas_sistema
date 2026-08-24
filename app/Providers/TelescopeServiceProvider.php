<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            return (bool) config('observability.telescope_enabled');
        });
    }

    public function boot(): void
    {
        parent::boot();

        Telescope::auth(function ($request) {
            $user = $request->user('platform');

            return app()->environment('local') && $user !== null && $user->isAdmin();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        Telescope::hideRequestParameters([
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'sifen_partner_token',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'authorization',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user = null) {
            return $user instanceof \App\Models\PlatformUser && $user->isAdmin();
        });
    }
}
