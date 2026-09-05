<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const INTERVAL_MONTHLY = 'monthly';

    public const INTERVAL_YEARLY = 'yearly';

    public const INTERVAL_LIFETIME = 'lifetime';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'interval',
        'starts_at',
        'ends_at',
        'grace_ends_at',
        'readonly_ends_at',
        'status',
        'reminded_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'readonly_ends_at' => 'datetime',
        'reminded_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class);
    }

    public function isLifetime(): bool
    {
        return $this->interval === self::INTERVAL_LIFETIME;
    }

    public function endsLabel(): string
    {
        if ($this->isLifetime()) {
            return 'Sin vencimiento';
        }

        return $this->ends_at?->format('d/m/Y') ?: '—';
    }
}
