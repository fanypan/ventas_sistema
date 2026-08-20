<?php

namespace App\Services\Sifen;

use App\Models\SifenDocument;
use App\Services\Billing\PlanLimitService;
use Modules\Sales\Entities\Sale;

class SifenIssuer
{
    public function __construct(
        private SifenGateway $gateway,
        private PlanLimitService $limits,
    ) {
    }

    public function issueForSale(Sale $sale): ?SifenDocument
    {
        if (! $this->limits->hasFeature('sifen')) {
            return SifenDocument::create([
                'sale_id' => $sale->id,
                'document_type' => 'factura',
                'status' => SifenDocument::STATUS_SKIPPED,
                'error_message' => $this->limits->sifenLimitMessage(),
                'payload' => ['sale_id' => $sale->id],
            ]);
        }

        if (! $this->limits->canEmitSifenDocument()) {
            return SifenDocument::create([
                'sale_id' => $sale->id,
                'document_type' => 'factura',
                'status' => SifenDocument::STATUS_REJECTED,
                'error_message' => $this->limits->sifenLimitMessage(),
                'payload' => ['sale_id' => $sale->id],
            ]);
        }

        $payload = [
            'sale_id' => $sale->id,
            'total' => $sale->total,
            'ruc' => \App\Helpers\SettingHelper::getValue('company_nit'),
            'company' => \App\Helpers\SettingHelper::getValue('company_name'),
            'customer_id' => $sale->customer_id,
        ];

        $result = $this->gateway->issue($payload);

        return SifenDocument::create([
            'sale_id' => $sale->id,
            'document_type' => 'factura',
            'cdc' => $result['cdc'] ?? null,
            'status' => $result['status'] ?? SifenDocument::STATUS_PENDING,
            'partner_reference' => $result['reference'] ?? null,
            'payload' => $payload,
            'response' => $result['response'] ?? null,
            'error_message' => $result['error'] ?? null,
            'issued_at' => now(),
        ]);
    }
}
