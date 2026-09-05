<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
        $this->seed(PlanSeeder::class);
        $this->seed(PlatformUserSeeder::class);
    }

    public function test_landing_returns_ok_without_staff_link(): void
    {
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('Starter')
            ->assertDontSee('Acceso staff');
    }

    public function test_platform_login_and_dashboard(): void
    {
        $path = config('saas.platform_path');

        $this->get("/{$path}/login")->assertOk();

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/login", [
                'email' => 'plataforma@arandutech.com',
                'password' => 'plataforma',
            ])->assertRedirect(route('platform.dashboard'));

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}")
            ->assertOk()
            ->assertSee('Panel')
            ->assertSee('Clientes')
            ->assertSee('Nuevo cliente');
    }

    public function test_platform_login_uses_rioplatense_copy(): void
    {
        $path = config('saas.platform_path');

        $this->get("/{$path}/login")
            ->assertOk()
            ->assertSee('Ingresá a la plataforma')
            ->assertSee('Usá tu correo de staff')
            ->assertSee('brand/favicon.svg', false)
            ->assertDontSee('Staff AranduTech');
    }

    public function test_platform_login_error_stays_in_the_form(): void
    {
        $path = config('saas.platform_path');

        $this->from("/{$path}/login")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->followingRedirects()
            ->post("/{$path}/login", [
                'email' => 'nadie@arandutech.com',
                'password' => 'incorrecta',
            ])
            ->assertOk()
            ->assertSee('Ese correo o contraseña no coinciden')
            ->assertSee('Ingresá a la plataforma');
    }

    public function test_platform_tenants_index_shows_empty_state(): void
    {
        $path = config('saas.platform_path');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes")
            ->assertOk()
            ->assertSee('Todavía no hay clientes');
    }

    public function test_platform_rejects_tenant_slug_with_hyphen_or_underscore(): void
    {
        $path = config('saas.platform_path');
        $plan = Plan::first();
        $payload = [
            'name' => 'Mi negocio',
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@test.com',
            'interval' => 'monthly',
        ];

        $this->actingAs(PlatformUser::first(), 'platform')
            ->from("/{$path}/clientes/nuevo")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes", $payload + ['slug' => 'mi-negocio'])
            ->assertSessionHasErrors('slug');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->from("/{$path}/clientes/nuevo")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes", $payload + ['slug' => 'mi_negocio'])
            ->assertSessionHasErrors('slug');
    }

    public function test_platform_login_ignores_stale_tenant_web_session(): void
    {
        $path = config('saas.platform_path');
        $webSessionKey = $this->app['auth']->guard('web')->getName();

        $this->withSession([$webSessionKey => 2])
            ->get("/{$path}/login")
            ->assertOk()
            ->assertSee('Ingresá a la plataforma');
    }
}
