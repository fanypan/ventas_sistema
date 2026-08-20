<?php

namespace App\Services\Sifen;

use Illuminate\Support\Facades\Http;

class PartnerSifenGateway implements SifenGateway
{
    public function issue(array $document): array
    {
        $url = rtrim((string) config('saas.sifen_partner_url'), '/');
        $token = config('saas.sifen_partner_token');

        if ($url === '' || ! $token) {
            return [
                'status' => 'rejected',
                'error' => 'Falta SIFEN_PARTNER_URL o SIFEN_PARTNER_TOKEN.',
            ];
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->post($url.'/documents', $document);

        if ($response->failed()) {
            return [
                'status' => 'rejected',
                'error' => $response->json('message') ?? 'El partner SIFEN rechazó el documento.',
                'response' => $response->json() ?? ['body' => $response->body()],
            ];
        }

        $payload = $response->json() ?? [];

        return [
            'status' => $payload['status'] ?? 'sent',
            'cdc' => $payload['cdc'] ?? null,
            'reference' => $payload['id'] ?? $payload['reference'] ?? null,
            'response' => $payload,
        ];
    }
}
