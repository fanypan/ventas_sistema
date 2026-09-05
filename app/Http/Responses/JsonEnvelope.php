<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JsonEnvelope
{
    /**
     * @var list<string>
     */
    private const GENERIC_MESSAGES = [
        'Unauthenticated.',
        'This action is unauthorized.',
        'Server Error',
        'Not Found.',
        'CSRF token mismatch.',
    ];

    /**
     * @param  array<string, mixed>|null  $data
     * @return array{status: string, message: string, data: array<string, mixed>|null}
     */
    public static function payload(string $status, string $message, ?array $data = null): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function success(string $message, ?array $data = null, int $status = 200): JsonResponse
    {
        return response()->json(self::payload('success', $message, $data), $status);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function error(string $message, ?array $data = null, int $status = 400): JsonResponse
    {
        return response()->json(self::payload('error', $message, $data), $status);
    }

    public static function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    public static function isExempt(Request $request): bool
    {
        return $request->is('file-manager', 'file-manager/*');
    }

    public static function isEnvelope(mixed $payload): bool
    {
        return is_array($payload)
            && isset($payload['status'])
            && in_array($payload['status'], ['success', 'error'], true)
            && array_key_exists('message', $payload)
            && array_key_exists('data', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromFrameworkPayload(array $payload, int $status): JsonResponse
    {
        $message = is_string($payload['message'] ?? null) ? $payload['message'] : '';
        $data = null;

        if (isset($payload['errors']) && is_array($payload['errors']) && ! array_is_list($payload['errors'])) {
            $data = $payload['errors'];
        }

        if (self::shouldReplaceMessage($message, $status)) {
            $message = self::messageForStatus($status);
        }

        return self::error($message, $data, $status);
    }

    public static function messageForStatus(int $status): string
    {
        return match ($status) {
            401 => 'Tenés que iniciar sesión.',
            403 => 'No tenés permiso para esto.',
            404 => 'No encontramos lo que buscás.',
            419 => 'La página venció. Recargá e intentá de nuevo.',
            429 => 'Demasiados intentos. Esperá un toque.',
            503 => 'El servicio no está disponible.',
            default => 'Algo salió mal. Probá de nuevo.',
        };
    }

    private static function shouldReplaceMessage(string $message, int $status): bool
    {
        if ($message === '' || in_array($message, self::GENERIC_MESSAGES, true)) {
            return true;
        }

        return $status === 404 && (
            str_starts_with($message, 'The route ')
            || str_starts_with($message, 'No query results for model')
        );
    }
}
