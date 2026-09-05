<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\SifenDocument;
use App\Models\Tenant;
use App\Models\User;
use Modules\Financials\Entities\Caja;

class PlanLimitService
{
    public function __construct(private ?Tenant $tenant = null)
    {
        $this->tenant ??= tenant();
    }

    public function plan(): ?Plan
    {
        $tenant = $this->tenant ?? tenant();

        return $tenant?->plan;
    }

    public function canCreateUser(): bool
    {
        $plan = $this->plan();
        if (! $plan || $plan->max_users <= 0) {
            return true;
        }

        return User::count() < $plan->max_users;
    }

    public function canOpenCaja(): bool
    {
        $plan = $this->plan();
        if (! $plan || $plan->max_cajas <= 0) {
            return true;
        }

        return Caja::open()->count() < $plan->max_cajas;
    }

    public function canEmitSifenDocument(): bool
    {
        $plan = $this->plan();
        if (! $plan || ! $plan->hasFeature('sifen')) {
            return false;
        }

        if ($plan->sifen_documents_monthly <= 0) {
            return true;
        }

        return $this->sifenDocumentsThisMonth() < $plan->sifen_documents_monthly;
    }

    public function sifenDocumentsThisMonth(): int
    {
        return SifenDocument::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->countsTowardQuota()
            ->count();
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) $this->plan()?->hasFeature($feature);
    }

    public function featureDeniedMessage(string $feature): string
    {
        $labels = [
            'purchases' => 'compras y proveedores',
            'credits' => 'créditos y ventas a crédito',
            'sifen' => 'facturación electrónica',
        ];

        $label = $labels[$feature] ?? $feature;

        return 'Tu plan no incluye '.$label.'. Contactá a AranduTech para ampliar.';
    }

    public function userLimitMessage(): string
    {
        $plan = $this->plan();

        return 'El plan '.$plan?->name.' permite hasta '.$plan?->max_users.' usuarios. Contactá a AranduTech para ampliar.';
    }

    public function cajaLimitMessage(): string
    {
        $plan = $this->plan();

        return 'El plan '.$plan?->name.' permite hasta '.$plan?->max_cajas.' cajas abiertas a la vez. Contactá a AranduTech para ampliar.';
    }

    public function cajaAlreadyOpenMessage(): string
    {
        return 'Ya tenés una caja abierta. Cerrala antes de abrir otra, o andá a vender.';
    }

    public function sifenLimitMessage(): string
    {
        $plan = $this->plan();

        if (! $plan?->hasFeature('sifen')) {
            return 'La facturación electrónica no está incluida en tu plan.';
        }

        return 'Alcanzaste el cupo de '.$plan->sifen_documents_monthly.' documentos SIFEN de este mes.';
    }
}
