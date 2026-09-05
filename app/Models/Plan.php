<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_cajas',
        'sifen_documents_monthly',
        'features',
        'description',
        'is_active',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
        'max_users' => 'integer',
        'max_cajas' => 'integer',
        'sifen_documents_monthly' => 'integer',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    /**
     * Planes internos (on-prem) no vencen, aunque el alta mande mensual/anual.
     */
    public function subscriptionInterval(string $requested): string
    {
        if (! $this->is_public) {
            return Subscription::INTERVAL_LIFETIME;
        }

        return $requested;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function listedOnLanding(Builder $query): void
    {
        $query->active()->where('is_public', true);
    }
}
