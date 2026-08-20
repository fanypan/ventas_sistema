<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $central = config('tenancy.central_domains', []);

        if (! in_array($host, $central, true)) {
            abort(404);
        }

        return $next($request);
    }
}
