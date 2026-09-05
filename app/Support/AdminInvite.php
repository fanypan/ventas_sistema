<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;

final class AdminInvite
{
    public static function url(Tenant $tenant): string
    {
        $tenantUrl = $tenant->url();
        $tenantScheme = parse_url($tenantUrl, PHP_URL_SCHEME);

        URL::forceRootUrl($tenantUrl);

        if (is_string($tenantScheme) && $tenantScheme !== '') {
            URL::forceScheme($tenantScheme);
        }

        try {
            return URL::temporarySignedRoute(
                'password.setup.show',
                now()->addHours((int) config('saas.admin_invite_hours', 48))
            );
        } finally {
            URL::forceRootUrl(null);
            URL::forceScheme(null);
        }
    }
}
