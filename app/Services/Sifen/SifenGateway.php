<?php

namespace App\Services\Sifen;

interface SifenGateway
{
    /**
     * @param  array<string, mixed>  $document
     * @return array{status: string, cdc?: string, reference?: string, response?: array, error?: string}
     */
    public function issue(array $document): array;
}
