<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Hosts that Symfony/Laravel may accept (Host header).
     *
     * APP_URL alone is not enough: on-prem often leaves APP_URL as localhost
     * while CENTRAL_DOMAINS / TENANT_BASE_DOMAIN are the real shop hosts.
     * Loopback is required so Nginx can health-check GET /up.
     *
     * @return array<int, string|null>
     */
    public function hosts()
    {
        $patterns = [
            $this->allSubdomainsOfApplicationUrl(),
            '^localhost$',
            '^127\.0\.0\.1$',
        ];

        foreach (config('tenancy.central_domains', []) as $domain) {
            if (! is_string($domain) || $domain === '') {
                continue;
            }

            $patterns[] = '^'.preg_quote($domain).'$';
        }

        $tenantBase = config('saas.tenant_base_domain');
        if (is_string($tenantBase) && $tenantBase !== '') {
            $patterns[] = '^(.+\.)?'.preg_quote($tenantBase).'$';
        }

        return array_values(array_unique(array_filter($patterns)));
    }
}
