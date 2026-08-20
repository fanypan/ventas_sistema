<?php

namespace App\Services\Sifen;

class NullSifenGateway implements SifenGateway
{
    public function issue(array $document): array
    {
        return [
            'status' => 'skipped',
            'error' => 'SIFEN no está configurado. Definí SIFEN_DRIVER=partner cuando el conector esté listo.',
        ];
    }
}
