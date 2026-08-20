<?php

namespace Tests\Feature;

use App\Models\PlatformUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\PlatformUserSeeder::class);
    }

    public function test_landing_returns_ok(): void
    {
        $this->get('http://localhost/')->assertOk()->assertSee('Starter');
    }

    public function test_platform_login_and_dashboard(): void
    {
        $this->get('/plataforma/login')->assertOk();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/plataforma/login', [
                'email' => 'plataforma@arandutech.com',
                'password' => 'plataforma',
            ])->assertRedirect(route('platform.dashboard'));

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get('/plataforma')
            ->assertOk();
    }
}
