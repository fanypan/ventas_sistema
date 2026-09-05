<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlatformPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user('platform');

        if ($user === null || ! $user->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
