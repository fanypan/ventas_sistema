<?php

namespace Tests\Feature;

use App\Http\Responses\JsonEnvelope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.checks.app', 'ok')
            ->assertJsonPath('data.checks.database', 'ok')
            ->assertJsonPath('data.checks.storage', 'ok')
            ->assertJsonPath('data.checks.redis', 'skipped')
            ->assertJsonPath('data.checks.minio', 'skipped');
    }

    public function test_health_endpoint_json_404_uses_envelope_when_disabled(): void
    {
        config(['observability.health_enabled' => false]);

        $this->getJson('/up')
            ->assertNotFound()
            ->assertJson([
                'status' => 'error',
                'message' => JsonEnvelope::messageForStatus(404),
                'data' => null,
            ]);
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
