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

        $this->forgetForeignGuard($request);

        foreach ($this->guardsForCurrentContext() as $guard) {
            $user = $request->user($guard);
            if ($user === null) {
                continue;
            }

            $this->validateGuard($request, $guard, $user);
        }

        return tap($next($request), function () use ($request) {
            foreach ($this->guardsForCurrentContext() as $guard) {
                $user = $request->user($guard);
                if ($user !== null) {
                    $request->session()->put('password_hash_'.$guard, $user->getAuthPassword());
                }
            }
        });
    }

    /**
     * @return list<string>
     */
    private function guardsForCurrentContext(): array
    {
        return tenancy()->initialized ? ['web'] : ['platform'];
    }

    private function forgetForeignGuard(Request $request): void
    {
        $foreign = tenancy()->initialized ? 'platform' : 'web';
        $auth = $this->auth->guard($foreign);

        $request->session()->forget($auth->getName());
        $request->session()->forget('password_hash_'.$foreign);

        if (method_exists($auth, 'getRecallerName')) {
            $recaller = $auth->getRecallerName();
            $request->cookies->remove($recaller);
            cookie()->queue(cookie()->forget($recaller));
        }
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

        throw new AuthenticationException(
            'Unauthenticated.',
            [$guard],
            $guard === 'platform' ? route('platform.login') : route('login')
        );
    }
}
