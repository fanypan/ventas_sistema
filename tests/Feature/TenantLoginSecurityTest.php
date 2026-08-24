<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TenantTestCase;

class TenantLoginSecurityTest extends TenantTestCase
{
    public function test_login_regenerates_session_id(): void
    {
        $this->tenantGet('/');
        $before = $this->app['session']->getId();

        $this->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->post('http://demo.localhost/', [
                'email' => 'admin@demo.test',
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertAuthenticated('web');
        $this->assertNotSame($before, $this->app['session']->getId());
    }

    public function test_remember_me_is_off_unless_checked(): void
    {
        $recaller = Auth::guard('web')->getRecallerName();

        $this->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->post('http://demo.localhost/', [
                'email' => 'admin@demo.test',
                'password' => 'password',
            ])
            ->assertRedirect()
            ->assertCookieMissing($recaller);
    }

    public function test_remember_me_sets_recaller_when_checked(): void
    {
        $recaller = Auth::guard('web')->getRecallerName();

        $this->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->post('http://demo.localhost/', [
                'email' => 'admin@demo.test',
                'password' => 'password',
                'remember' => '1',
            ])
            ->assertRedirect()
            ->assertCookie($recaller);
    }
}
