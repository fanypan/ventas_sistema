<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlatformAccess
{
    public function handle(Request $request, Closure $next)
    {
        $path = config('saas.platform_path');
        $domain = config('saas.platform_domain');

        if ($path !== 'plataforma' && $request->is('plataforma', 'plataforma/*')) {
            abort(404);
        }

        if ($domain && $request->getHost() !== $domain) {
            $allowedOnCentral = app()->environment('local')
                && in_array($request->getHost(), config('tenancy.central_domains', []), true);

            if (! $allowedOnCentral) {
                abort(404);
            }
        }

        return $next($request);
    }
}
