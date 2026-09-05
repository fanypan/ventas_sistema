<?php

namespace Tests\Unit;

use App\Http\Responses\JsonEnvelope;
use Tests\TestCase;

class JsonEnvelopeTest extends TestCase
{
    public function test_payload_shape(): void
    {
        $this->assertSame([
            'status' => 'error',
            'message' => 'Stock insuficiente',
            'data' => null,
        ], JsonEnvelope::payload('error', 'Stock insuficiente'));
    }

    public function test_from_framework_validation_payload(): void
    {
        $response = JsonEnvelope::fromFrameworkPayload([
            'message' => 'The product id field is required.',
            'errors' => [
                'product_id' => ['The product id field is required.'],
            ],
        ], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'status' => 'error',
            'message' => 'The product id field is required.',
            'data' => [
                'product_id' => ['The product id field is required.'],
            ],
        ], $response->getData(true));
    }

    public function test_from_framework_replaces_generic_message(): void
    {
        $response = JsonEnvelope::fromFrameworkPayload([
            'message' => 'Unauthenticated.',
        ], 401);

        $this->assertSame([
            'status' => 'error',
            'message' => 'Tenés que iniciar sesión.',
            'data' => null,
        ], $response->getData(true));
    }

    public function test_from_framework_replaces_route_not_found_message(): void
    {
        $response = JsonEnvelope::fromFrameworkPayload([
            'message' => 'The route ruta-inexistente could not be found.',
            'exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
        ], 404);

        $this->assertSame([
            'status' => 'error',
            'message' => 'No encontramos lo que buscás.',
            'data' => null,
        ], $response->getData(true));
    }
}
