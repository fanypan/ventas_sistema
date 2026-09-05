<?php

namespace Tests\Feature;

use App\Http\Responses\JsonEnvelope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
    }

    public function test_central_404_shows_custom_page_with_home_link(): void
    {
        $this->get('http://localhost/ruta-inexistente')
            ->assertNotFound()
            ->assertSee('Página no encontrada')
            ->assertSee('Ir al inicio')
            ->assertDontSee('Tenant could not be identified');
    }

    public function test_unknown_tenant_domain_returns_404_without_landing_link(): void
    {
        $this->get('http://demo.localhost/')
            ->assertNotFound()
            ->assertSee('Página no encontrada')
            ->assertDontSee('Ir al inicio')
            ->assertDontSee('Tenant could not be identified');
    }

    public function test_custom_error_pages_are_rendered_for_http_codes(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/__test-error-403', fn () => abort(403));
            Route::get('/__test-error-500', fn () => abort(500));
        });

        $this->get('http://localhost/__test-error-403')
            ->assertForbidden()
            ->assertSee('Acceso denegado');

        $this->get('http://localhost/__test-error-500')
            ->assertStatus(500)
            ->assertSee('Error interno');
    }

    public function test_json_404_uses_envelope(): void
    {
        $this->getJson('http://localhost/ruta-inexistente')
            ->assertNotFound()
            ->assertJson([
                'status' => 'error',
                'message' => JsonEnvelope::messageForStatus(404),
                'data' => null,
            ]);
    }

    public function test_unknown_tenant_json_uses_envelope(): void
    {
        $this->getJson('http://demo.localhost/')
            ->assertNotFound()
            ->assertJson([
                'status' => 'error',
                'message' => JsonEnvelope::messageForStatus(404),
                'data' => null,
            ]);
    }

    public function test_json_http_exceptions_use_envelope(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/__test-error-403', fn () => abort(403));
        });

        $this->getJson('http://localhost/__test-error-403')
            ->assertForbidden()
            ->assertJson([
                'status' => 'error',
                'message' => JsonEnvelope::messageForStatus(403),
                'data' => null,
            ]);
    }
}
