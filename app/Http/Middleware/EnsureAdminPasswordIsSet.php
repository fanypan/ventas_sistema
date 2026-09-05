<?php

namespace App\Http\Middleware;

use App\Http\Responses\JsonEnvelope;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminPasswordIsSet
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('password.setup.show', 'password.setup.store')) {
            return $next($request);
        }

        $user = $request->user('web');

        if (! $user instanceof User || ! $user->must_change_password) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Definí tu contraseña con el enlace que te mandamos por mail.';

        if (JsonEnvelope::wantsJson($request)) {
            return JsonEnvelope::error($message, null, 403);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
