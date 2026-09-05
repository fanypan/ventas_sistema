<?php

namespace Tests\Feature;

use App\Models\PlatformUser;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnpremPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
        $this->seed(PlanSeeder::class);
        $this->seed(PlatformUserSeeder::class);
    }

    public function test_landing_hides_onprem_plan(): void
    {
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Negocio')
            ->assertDontSee('Instalación propia');
    }

    public function test_platform_create_form_lists_onprem_plan(): void
    {
        $path = config('saas.platform_path');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes/nuevo")
            ->assertOk()
            ->assertSee('Instalación propia')
            ->assertSee('sin vencimiento')
            ->assertSee('Sin vencimiento (on-premise)');
    }
}
