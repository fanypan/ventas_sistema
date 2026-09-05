<?php

namespace App\Exceptions;

use App\Http\Responses\JsonEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RenderJsonEnvelope
{
    public function __invoke(Response $response, Throwable $e, Request $request): Response
    {
        if (! JsonEnvelope::wantsJson($request) || JsonEnvelope::isExempt($request)) {
            return $response;
        }

        $payload = $this->jsonPayload($response);

        if (! is_array($payload) || JsonEnvelope::isEnvelope($payload)) {
            return $response;
        }

        return JsonEnvelope::fromFrameworkPayload($payload, $response->getStatusCode());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function jsonPayload(Response $response): ?array
    {
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            return is_array($data) ? $data : null;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'json')) {
            return null;
        }

        $decoded = json_decode((string) $response->getContent(), true);

        return is_array($decoded) ? $decoded : null;
    }
}
