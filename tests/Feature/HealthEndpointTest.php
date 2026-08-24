<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.app', 'ok')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.storage', 'ok')
            ->assertJsonPath('checks.redis', 'skipped');
    }

    public function test_health_endpoint_is_hidden_when_disabled(): void
    {
        config(['observability.health_enabled' => false]);

        $this->get('/up')->assertNotFound();
    }

    public function test_horizon_and_telescope_are_off_by_default(): void
    {
        $path = config('saas.platform_path');

        $this->get("http://localhost/{$path}/horizon")->assertNotFound();
        $this->get("http://localhost/{$path}/telescope")->assertNotFound();
    }
}
