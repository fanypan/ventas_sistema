<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

class AuthenticateSession
{
    public function __construct(protected AuthFactory $auth) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        foreach (['web', 'platform'] as $guard) {
            $user = $request->user($guard);
            if ($user === null) {
                continue;
            }

            $this->validateGuard($request, $guard, $user);
        }

        return tap($next($request), function () use ($request) {
            foreach (['web', 'platform'] as $guard) {
                $user = $request->user($guard);
                if ($user !== null) {
                    $request->session()->put('password_hash_'.$guard, $user->getAuthPassword());
                }
            }
        });
    }

    private function validateGuard(Request $request, string $guard, object $user): void
    {
        $auth = $this->auth->guard($guard);

        if (method_exists($auth, 'viaRemember') && $auth->viaRemember()) {
            $passwordHash = explode('|', (string) $request->cookies->get($auth->getRecallerName()))[2] ?? null;

            if (! $passwordHash || $passwordHash !== $user->getAuthPassword()) {
                $this->logout($request, $guard);
            }
        }

        $key = 'password_hash_'.$guard;

        if (! $request->session()->has($key)) {
            $request->session()->put($key, $user->getAuthPassword());
        }

        if ($request->session()->get($key) !== $user->getAuthPassword()) {
            $this->logout($request, $guard);
        }
    }

    private function logout(Request $request, string $guard): void
    {
        $this->auth->guard($guard)->logout();
        $request->session()->flush();

        throw new AuthenticationException('Unauthenticated.', [$guard]);
    }
}
