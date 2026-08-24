<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\PlatformUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class AuthenticateSessionTest extends TenantTestCase
{
    public function test_tenant_session_is_invalidated_after_password_change(): void
    {
        $this->tenantPost('/', [
            'email' => 'admin@demo.test',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticated('web');

        $this->tenant->run(function () {
            $user = User::where('email', 'admin@demo.test')->firstOrFail();
            $user->password = Hash::make('nuevaclave99');
            $user->save();
        });

        // El guard de la suite reutiliza el User en memoria; en php-fpm cada request lo lee de la DB.
        $this->app['auth']->forgetGuards();

        $this->tenantGet('/admin/dashboard')->assertRedirect();
        $this->assertGuest('web');
    }

    public function test_platform_session_is_invalidated_after_password_change(): void
    {
        $path = config('saas.platform_path');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/login", [
                'email' => 'plataforma@arandutech.com',
                'password' => 'plataforma',
            ])
            ->assertRedirect();

        $this->assertAuthenticated('platform');

        $user = PlatformUser::query()->firstOrFail();
        $user->password = Hash::make('nuevaclave99');
        $user->save();

        $this->app['auth']->forgetGuards();

        $this->get("/{$path}")
            ->assertRedirect(route('platform.login'));
        $this->assertGuest('platform');
    }

    public function test_acting_as_still_reaches_the_tenant_dashboard(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/dashboard')
            ->assertOk();
    }

    public function test_tenant_login_ignores_stale_platform_session(): void
    {
        $platformSessionKey = $this->app['auth']->guard('platform')->getName();

        $this->withSession([$platformSessionKey => 1])
            ->tenantGet('/')
            ->assertOk();
    }
}
