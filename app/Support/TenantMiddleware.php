<?php

namespace App\Support;

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenantMiddleware
{
    public static function web(): array
    {
        return [
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            'tenant.subscription',
        ];
    }
}
