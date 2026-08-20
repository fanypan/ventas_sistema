<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_GRACE = 'grace';
    public const STATUS_READONLY = 'readonly';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'ruc',
            'status',
            'plan_id',
            'admin_name',
            'admin_email',
            'admin_password_hash',
            'brand_color',
            'provisioned_at',
        ];
    }

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    public function primaryDomain(): string
    {
        return $this->domains->first()?->domain
            ?? $this->slug . '.' . config('saas.tenant_base_domain');
    }

    public function url(): string
    {
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
        $port = parse_url(config('app.url'), PHP_URL_PORT);
        $host = $this->primaryDomain();

        return $scheme . '://' . $host . ($port ? ':' . $port : '');
    }

    public function isOperational(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_GRACE], true);
    }

    public function isReadOnly(): bool
    {
        return $this->status === self::STATUS_READONLY;
    }

    public function isBlocked(): bool
    {
        return in_array($this->status, [self::STATUS_SUSPENDED, self::STATUS_CANCELLED, self::STATUS_PENDING], true);
    }
}
