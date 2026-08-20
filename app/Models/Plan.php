<?php

namespace App\Models;

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
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
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
}
